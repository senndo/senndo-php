<?php

declare(strict_types=1);

namespace Senndo;

use Senndo\Exception\ConnectionException;
use Senndo\Exception\RequestException;
use Senndo\Exception\TimeoutException;

/**
 * Le transport par défaut : cURL, zéro dépendance.
 *
 * `CURLOPT_FAILONERROR` N'EST PAS UTILISÉ, DÉLIBÉRÉMENT. Il ferait échouer cURL sur un statut ≥ 400
 * et jetterait le corps de la réponse — c'est-à-dire l'enveloppe d'erreur, donc le code stable sur
 * lequel l'appelant branche. Un 402 deviendrait « erreur cURL 22 », et — pire — serait traité comme
 * une panne de transport, donc retentable.
 */
final class CurlTransport implements Transport
{
    public function send(HttpRequest $request): HttpResponse
    {
        // Une requête sans URL ni verbe est une faute de programmation, pas une panne de réseau :
        // elle lève une `RequestException`, que la boucle de retentative ne rattrape pas. La
        // rendre en `ConnectionException` la ferait rejouer deux fois avant d'échouer pareil.
        if ($request->url === '' || $request->method === '') {
            throw new RequestException('senndo : requête sans URL ou sans verbe HTTP.');
        }

        $handle = curl_init();
        if ($handle === false) {
            throw new ConnectionException('curl', 'curl_init a échoué');
        }

        $headers = [];
        foreach ($request->headers as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        /** @var array<string, string> $responseHeaders */
        $responseHeaders = [];

        // Les options sont posées UNE PAR UNE, et non par `curl_setopt_array` : la signature de la
        // variante tableau exige des chaînes non vides pour l'URL et le verbe, ce que le type d'une
        // `HttpRequest` ne promet pas. Contraindre la requête à `non-empty-string` propagerait la
        // contrainte jusque dans le contrat généré pour un gain nul — l'URL est construite par le
        // SDK, jamais reçue de l'extérieur.
        curl_setopt($handle, CURLOPT_URL, $request->url);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $request->method);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, false);
        // Le délai porte sur l'ÉCHANGE COMPLET, pas seulement sur la connexion : un serveur qui
        // accepte la connexion puis ne répond jamais épuiserait sinon le worker appelant.
        curl_setopt($handle, CURLOPT_TIMEOUT_MS, (int) ($request->timeout * 1000));
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT_MS, (int) (min($request->timeout, 10.0) * 1000));
        curl_setopt(
            $handle,
            CURLOPT_HEADERFUNCTION,
            static function (\CurlHandle $_handle, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $separator = strpos($line, ':');
                if ($separator !== false) {
                    $name = strtolower(trim(substr($line, 0, $separator)));
                    $responseHeaders[$name] = trim(substr($line, $separator + 1));
                }

                return $length;
            },
        );

        if ($request->body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $request->body);
        }

        $body = curl_exec($handle);
        $errorCode = curl_errno($handle);
        $errorMessage = curl_error($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($errorCode === CURLE_OPERATION_TIMEDOUT) {
            throw new TimeoutException($request->timeout, $request->url);
        }
        if ($errorCode !== 0 || !is_string($body)) {
            throw new ConnectionException($request->url, $errorMessage);
        }

        /** @var array<string, string> $responseHeaders */
        return new HttpResponse((int) $status, $responseHeaders, $body);
    }
}
