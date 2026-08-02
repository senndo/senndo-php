<?php

declare(strict_types=1);

namespace Senndo;

use Senndo\Exception\ApiException;
use Senndo\Exception\ConnectionException;
use Senndo\Exception\ProtocolException;
use Senndo\Exception\RateLimitException;
use Senndo\Exception\SenndoException;
use Senndo\Exception\TimeoutException;

/**
 * Le transport : URL, en-têtes, délais, retentatives, multipart, erreurs typées.
 *
 * LA POLITIQUE DE RETENTATIVE EST LA PARTIE DANGEREUSE DE CE FICHIER, et elle tient en une phrase :
 * **on ne rejoue que ce qui est rejouable**. Rejouer un `POST /v1/messages` sans clé d'idempotence,
 * c'est facturer deux messages ; rejouer un `POST /v1/webhooks`, c'est créer deux endpoints, donc
 * DOUBLER toutes les livraisons futures. Un enrobage qui retenterait « trois fois sur erreur » fait
 * exactement cela, en silence, et la facture arrive plus tard.
 *
 * La règle appliquée, sans exception :
 *   - `GET` et `DELETE` sont rejouables — la sémantique HTTP le garantit ;
 *   - un `POST` n'est rejouable QUE s'il porte une clé d'idempotence non vide dans son corps ;
 *   - un `POST` sans clé n'est JAMAIS rejoué, même sur 503, même sur coupure réseau.
 * Et, quelle que soit la rejouabilité, on ne retente que sur un échec de TRANSPORT, un 429 ou un
 * 5xx. Un 4xx rejoué à l'identique échoue à l'identique.
 */
final class Http
{
    /** @var array<int, class-string<ApiException>> */
    private const CLASS_BY_STATUS = [
        400 => Exception\ValidationException::class,
        401 => Exception\AuthException::class,
        402 => Exception\InsufficientFundsException::class,
        403 => Exception\ForbiddenException::class,
        404 => Exception\NotFoundException::class,
        409 => Exception\ConflictException::class,
        413 => Exception\PayloadTooLargeException::class,
        415 => Exception\UnsupportedMediaTypeException::class,
        422 => Exception\ValidationException::class,
        429 => Exception\RateLimitException::class,
        503 => Exception\ServiceUnavailableException::class,
    ];

    /**
     * Masque une clé API : le préfixe reste lisible (il dit l'environnement), le reste non.
     */
    public static function maskApiKey(string $apiKey): string
    {
        if (strlen($apiKey) <= 8) {
            return '…';
        }

        return substr($apiKey, 0, 8) . '…' . (strlen($apiKey) - 8) . ' caractères masqués';
    }

    /**
     * Fabrique une clé d'idempotence aléatoire — À N'UTILISER QUE SI VOUS N'EN AVEZ PAS.
     *
     * Le SDK n'en pose JAMAIS à votre place. Une clé inventée au moment de l'appel ne protège de
     * rien de plus que ce que le transport fait déjà : elle est perdue si le processus meurt entre
     * l'envoi et la réponse, exactement le cas où l'idempotence sert. La bonne clé vient de VOTRE
     * domaine — l'identifiant de la commande, de la tentative de connexion, de la ligne de campagne.
     */
    public static function newIdempotencyKey(string $prefix = ''): string
    {
        return $prefix . bin2hex(random_bytes(16));
    }

    /**
     * Construit l'URL en substituant les paramètres de chemin et en sérialisant la requête.
     *
     * @param array{path: string, pathParams: list<string>, queryParams: list<string>, ...} $descriptor
     * @param array<string, string>                                                         $pathValues
     * @param array<string, mixed>                                                          $query
     */
    public static function buildUrl(
        string $baseUrl,
        array $descriptor,
        array $pathValues,
        array $query,
    ): string {
        $path = $descriptor['path'];
        foreach ($descriptor['pathParams'] as $name) {
            $value = $pathValues[$name] ?? '';
            if ($value === '') {
                throw new Exception\RequestException(
                    sprintf('senndo : paramètre de chemin « %s » manquant', $name),
                );
            }
            $path = str_replace('{' . $name . '}', rawurlencode($value), $path);
        }

        $pairs = [];
        foreach ($descriptor['queryParams'] as $name) {
            $value = $query[$name] ?? null;
            // `null` vaut « non fourni » : un client qui passe le résultat d'un champ de formulaire
            // vide ne doit pas envoyer « ?status= ».
            if ($value === null || $value === '') {
                continue;
            }
            if (is_bool($value)) {
                $pairs[$name] = $value ? 'true' : 'false';
            } elseif (is_scalar($value)) {
                $pairs[$name] = (string) $value;
            }
        }

        $base = str_ends_with($baseUrl, '/') ? substr($baseUrl, 0, -1) : $baseUrl;
        $suffix = $pairs === [] ? '' : '?' . http_build_query($pairs);

        return $base . $path . $suffix;
    }

    /**
     * Encode un téléversement en corps `multipart/form-data`, sans dépendance.
     *
     * @return array{0: string, 1: string} Le corps, puis la valeur de `Content-Type`.
     */
    public static function encodeMultipart(MultipartUpload $upload): array
    {
        $boundary = '----senndo' . bin2hex(random_bytes(16));
        $fileName = str_replace('"', '', $upload->fileName);
        $body = "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"file\"; filename=\"{$fileName}\"\r\n"
            . "Content-Type: {$upload->contentType}\r\n\r\n"
            . $upload->file
            . "\r\n--{$boundary}--\r\n";

        return [$body, "multipart/form-data; boundary={$boundary}"];
    }

    /**
     * Cet appel peut-il être REJOUÉ sans changer l'état du compte ?
     *
     * Exposé parce que c'est la décision la plus coûteuse du SDK : elle mérite d'être testée seule,
     * et lue sans dérouler la boucle de retentative.
     *
     * @param array{method: string, ...} $descriptor
     * @param array<string, mixed>|null  $body
     */
    public static function isReplayable(array $descriptor, ?array $body): bool
    {
        if ($descriptor['method'] === 'GET' || $descriptor['method'] === 'DELETE') {
            return true;
        }
        $key = $body['idempotencyKey'] ?? null;

        return is_string($key) && $key !== '';
    }

    /**
     * Cet échec vaut-il une retentative, indépendamment de la rejouabilité ?
     *
     * `null` = échec de transport.
     */
    public static function isTransient(?int $status): bool
    {
        return $status === null || $status === 429 || $status >= 500;
    }

    /**
     * Construit l'erreur typée d'une réponse d'échec.
     *
     * UN STATUT INCONNU NE FAIT PAS LEVER LE SDK sur autre chose que l'erreur du serveur : il
     * retombe sur `ApiException`. Une table exhaustive qui lèverait « statut non répertorié »
     * transformerait l'ajout d'un statut amont en panne chez tous les clients déjà déployés.
     */
    public static function errorFromResponse(
        int $status,
        string $rawBody,
        string $operationId,
        ?string $retryAfterHeader,
    ): SenndoException {
        /** @var mixed $parsed */
        $parsed = json_decode($rawBody, true);
        if (!is_array($parsed) || !isset($parsed['error']) || !is_array($parsed['error'])) {
            return new ProtocolException($status, substr($rawBody, 0, 500));
        }
        $envelope = $parsed['error'];
        if (!isset($envelope['code'], $envelope['message'])
            || !is_string($envelope['code'])
            || !is_string($envelope['message'])
        ) {
            return new ProtocolException($status, substr($rawBody, 0, 500));
        }

        $class = self::CLASS_BY_STATUS[$status] ?? ApiException::class;
        $error = new $class($status, $envelope['code'], $envelope['message'], $operationId);

        if ($error instanceof RateLimitException && $retryAfterHeader !== null) {
            $seconds = filter_var($retryAfterHeader, FILTER_VALIDATE_INT);
            $error->retryAfter = is_int($seconds) && $seconds >= 0 ? $seconds : null;
        }

        return $error;
    }

    /**
     * Exécute un appel : en-têtes, délai, retentatives, désérialisation, erreurs typées.
     *
     * @param array{operationId: string, method: string, path: string, pathParams: list<string>, queryParams: list<string>, ...} $descriptor
     * @param array<string, string>     $pathValues
     * @param array<string, mixed>      $query
     * @param array<string, mixed>|null $body
     * @param array<string, string>     $extraHeaders
     * @param callable(float): void     $sleep
     *
     * @return array<string, mixed>|null
     */
    public static function performRequest(
        Transport $transport,
        string $apiKey,
        string $baseUrl,
        string $userAgent,
        float $timeout,
        int $maxRetries,
        array $descriptor,
        array $pathValues = [],
        array $query = [],
        ?array $body = null,
        ?MultipartUpload $upload = null,
        array $extraHeaders = [],
        ?callable $sleep = null,
    ): ?array {
        $sleep ??= static function (float $seconds): void {
            usleep((int) ($seconds * 1_000_000));
        };
        $operationId = $descriptor['operationId'];
        $url = self::buildUrl($baseUrl, $descriptor, $pathValues, $query);
        $replayable = self::isReplayable($descriptor, $body);

        $headers = $extraHeaders;
        // Après l'étalement des en-têtes de l'appelant : `Authorization` n'est PAS surchargeable.
        // Le laisser l'être offrirait un moyen silencieux d'envoyer la requête d'un compte avec la
        // configuration d'un autre.
        $headers['Authorization'] = 'Bearer ' . $apiKey;
        $headers['Accept'] = 'application/json';
        $headers['User-Agent'] = $userAgent;

        $payload = null;
        if ($upload !== null) {
            [$payload, $contentType] = self::encodeMultipart($upload);
            $headers['Content-Type'] = $contentType;
        } elseif ($body !== null) {
            $encoded = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                throw new Exception\RequestException(
                    sprintf('senndo : %s — le corps n\'est pas sérialisable en JSON.', $operationId),
                );
            }
            $payload = $encoded;
            $headers['Content-Type'] = 'application/json';
        }

        $lastError = null;
        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            $request = new HttpRequest($descriptor['method'], $url, $headers, $payload, $timeout);
            $status = null;

            try {
                $response = $transport->send($request);
                if ($response->status >= 200 && $response->status < 300) {
                    if ($response->status === 204 || $response->body === '') {
                        return null;
                    }

                    return self::decodeJson($response->status, $response->body);
                }
                $status = $response->status;
                $lastError = self::errorFromResponse(
                    $response->status,
                    $response->body,
                    $operationId,
                    $response->headers['retry-after'] ?? null,
                );
            } catch (TimeoutException) {
                // L'exception est RECONSTRUITE avec l'identifiant de l'opération : un transport ne
                // connaît que l'URL, et « a dépassé le délai » sans nom d'opération oblige le
                // lecteur d'un log à remonter l'URL à la main pour savoir ce qui a échoué.
                $lastError = new TimeoutException($timeout, $operationId);
            } catch (ConnectionException $connectionError) {
                $lastError = new ConnectionException($operationId, $connectionError->detail);
            }

            if (!$replayable || !self::isTransient($status) || $attempt === $maxRetries) {
                throw $lastError;
            }
            $retryAfter = $lastError instanceof RateLimitException ? $lastError->retryAfter : null;
            $sleep(self::backoffSeconds($attempt, $retryAfter));
        }

        throw $lastError ?? new ConnectionException($operationId, 'état inatteignable');
    }

    /**
     * LES MONTANTS SONT DES CHAÎNES DÉCIMALES, et le décodage ne doit pas les convertir.
     *
     * `json_decode` sans `JSON_BIGINT_AS_STRING` transforme un entier trop grand en `float` — ce que
     * personne ne remarque tant que les identifiants sont courts, et qui corrompt silencieusement
     * une valeur le jour où ils ne le sont plus.
     *
     * @return array<string, mixed>
     */
    private static function decodeJson(int $status, string $rawBody): array
    {
        /** @var mixed $decoded */
        $decoded = json_decode($rawBody, true, 512, JSON_BIGINT_AS_STRING);
        if (!is_array($decoded)) {
            throw new ProtocolException($status, substr($rawBody, 0, 500));
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private static function backoffSeconds(int $attempt, ?int $retryAfter): float
    {
        if ($retryAfter !== null) {
            return min((float) $retryAfter, 60.0);
        }
        // Exponentiel plafonné, plein jitter : sans jitter, N clients qui échouent ensemble
        // retentent ensemble et reproduisent la surcharge qu'ils devaient laisser retomber.
        $ceiling = min(0.25 * (2 ** $attempt), 8.0);

        return (random_int(0, 1_000_000) / 1_000_000) * $ceiling;
    }
}
