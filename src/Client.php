<?php

declare(strict_types=1);

namespace Senndo;

use Senndo\Exception\RequestException;
use Senndo\Generated\ClientContract;
use Senndo\Generated\Contract;

/**
 * Le client HTTP de l'API senndo — une méthode par opération du contrat.
 *
 * LA CONFORMITÉ AU CONTRAT EST STRUCTURELLE. Cette classe implémente `ClientContract`, une
 * interface GÉNÉRÉE depuis le contrat : si une opération n'a pas sa méthode, ou si une signature
 * diverge, PHP refuse de charger la classe. Pas d'assertion à se souvenir de lancer, pas de table
 * de correspondance à tenir à jour.
 *
 * CE QUE CE FICHIER AJOUTE AU CONTRAT, et qu'aucun générateur ne donne :
 *   - une validation LOCALE avant l'appel — un champ obligatoire absent coûte zéro aller-retour, et
 *     sur un canal facturé, zéro risque de débit ;
 *   - la règle que `required` ne sait pas exprimer (D-122) : un envoi porte du contenu par au moins
 *     un de `text`, `media`, `template` ;
 *   - le rejet local des préfixes d'idempotence réservés, que le serveur refuse en 400 ;
 *   - la clé API MASQUÉE dans toute représentation de l'objet.
 *
 * @phpstan-import-type SendMessageBody from Contract
 * @phpstan-import-type SendMessageResponse from Contract
 * @phpstan-import-type EstimateMessageBody from Contract
 * @phpstan-import-type EstimateMessageResponse from Contract
 * @phpstan-import-type GetMessageResponse from Contract
 * @phpstan-import-type ListMessagesQuery from Contract
 * @phpstan-import-type ListMessagesResponse from Contract
 * @phpstan-import-type UploadMediaResponse from Contract
 * @phpstan-import-type ListMediaQuery from Contract
 * @phpstan-import-type ListMediaResponse from Contract
 * @phpstan-import-type ListPricesResponse from Contract
 * @phpstan-import-type GetBalanceQuery from Contract
 * @phpstan-import-type GetBalanceResponse from Contract
 * @phpstan-import-type ListCurrenciesResponse from Contract
 * @phpstan-import-type ListSenderIdsResponse from Contract
 * @phpstan-import-type ListLedgerQuery from Contract
 * @phpstan-import-type ListLedgerResponse from Contract
 * @phpstan-import-type ListInboxThreadsQuery from Contract
 * @phpstan-import-type ListInboxThreadsResponse from Contract
 * @phpstan-import-type ListInboxMessagesQuery from Contract
 * @phpstan-import-type ListInboxMessagesResponse from Contract
 * @phpstan-import-type ListWaTemplatesResponse from Contract
 * @phpstan-import-type ListWaCloudNumbersResponse from Contract
 * @phpstan-import-type GetRoutingCredentialsResponse from Contract
 * @phpstan-import-type ListWebhooksResponse from Contract
 * @phpstan-import-type CreateWebhookBody from Contract
 * @phpstan-import-type CreateWebhookResponse from Contract
 * @phpstan-import-type RevokeWebhookResponse from Contract
 * @phpstan-import-type ListWebhookDeliveriesQuery from Contract
 * @phpstan-import-type ListWebhookDeliveriesResponse from Contract
 */
final class Client implements ClientContract
{
    /** La version du paquet, vérifiée contre `composer.json` par un test. */
    public const SDK_VERSION = '1.1.0';

    /** Les préfixes d'idempotence que la plateforme se réserve (entrants, campagnes). */
    private const RESERVED_IDEMPOTENCY_PREFIXES = ['in:', 'cmp:'];

    private readonly string $baseUrl;

    private readonly Transport $transport;

    private readonly string $userAgent;

    public function __construct(
        private readonly string $apiKey,
        ?string $baseUrl = null,
        private readonly float $timeout = 30.0,
        private readonly int $maxRetries = 2,
        ?Transport $transport = null,
        ?string $userAgent = null,
    ) {
        if (trim($apiKey) === '') {
            throw new RequestException(
                'senndo : la clé API est requise. Créez une clé dans la console (« API » → '
                . '« Clés »). Une clé sk_test_ simule la livraison sans déplacer d\'argent.',
            );
        }
        $this->baseUrl = $baseUrl ?? self::envBaseUrl();
        $this->transport = $transport ?? new CurlTransport();
        $this->userAgent = $userAgent === null
            ? 'senndo-php/' . self::SDK_VERSION
            : 'senndo-php/' . self::SDK_VERSION . ' ' . $userAgent;
    }

    /**
     * `SENNDO_BASE_URL` surcharge la base — pour un bac à sable ou un miroir régional.
     *
     * Une valeur VIDE vaut absente : une variable d'environnement définie à la chaîne vide est le
     * cas normal d'un `.env` rempli à moitié, et `getenv() ?: default` la traiterait comme une base
     * d'URL valide.
     */
    private static function envBaseUrl(): string
    {
        $value = getenv('SENNDO_BASE_URL');

        return is_string($value) && $value !== '' ? $value : Contract::API_BASE_URL;
    }

    /**
     * La clé API, MASQUÉE.
     *
     * Il n'existe aucun accesseur qui la rende en clair : un SDK qui expose sa clé la voit finir
     * dans un log d'erreur, puis dans un agrégateur, puis hors du périmètre.
     */
    public function apiKeyMasked(): string
    {
        return Http::maskApiKey($this->apiKey);
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * `__toString` et `__debugInfo` sont le chemin par lequel un secret fuit vraiment.
     *
     * Un client posé dans un contexte d'exception, un `var_dump` de débogage, un dumper de
     * framework : chacun passe par l'un des deux. La clé n'y apparaît jamais en clair.
     */
    public function __toString(): string
    {
        return sprintf('Senndo\Client { baseUrl: %s, apiKey: %s }', $this->baseUrl, $this->apiKeyMasked());
    }

    /** @return array<string, string> */
    public function __debugInfo(): array
    {
        return ['baseUrl' => $this->baseUrl, 'apiKey' => $this->apiKeyMasked()];
    }

    // ── Envoi ────────────────────────────────────────────────────────────────────────────────

    /**
     * Envoie un message unitaire.
     *
     * `idempotencyKey` EST OBLIGATOIRE, et le SDK n'en fabrique pas à votre place — voir
     * `Http::newIdempotencyKey()`. Rejouer la même clé renvoie le message déjà créé avec
     * `replay: true`, sans jamais redébiter.
     *
     * @param SendMessageBody $body
     *
     * @return SendMessageResponse
     */
    public function sendMessage(array $body, ?RequestOptions $options = null): array
    {
        $this->assertRequiredBody('sendMessage', $body);

        // La règle que `required` ne peut pas porter (D-122) : le serveur exempte `text` quand le
        // contenu vit dans `media`, `template` ou `content` (modèle Twilio, `whatsapp_twilio`),
        // mais un envoi sans AUCUN des quatre n'a pas de contenu et part en 400. L'attraper ici
        // épargne l'aller-retour.
        if (
            !isset($body['text']) && !isset($body['media']) && !isset($body['template'])
            && !isset($body['content'])
        ) {
            throw new RequestException(
                'senndo : un envoi doit porter du contenu — renseignez « text », « media », '
                . '« template » ou « content ». Le texte n\'est facultatif que lorsque l\'un des '
                . 'trois autres le remplace.',
            );
        }
        foreach (self::RESERVED_IDEMPOTENCY_PREFIXES as $prefix) {
            if (str_starts_with($body['idempotencyKey'], $prefix)) {
                throw new RequestException(sprintf(
                    'senndo : le préfixe « %s » est réservé à la plateforme (entrants, campagnes). '
                    . 'Choisissez une clé d\'idempotence dérivée de VOTRE domaine.',
                    $prefix,
                ));
            }
        }

        /** @var SendMessageResponse */
        return $this->call('sendMessage', $options, body: $body);
    }

    /**
     * Estime le coût et le découpage d'un envoi, sans rien envoyer ni débiter.
     *
     * @param EstimateMessageBody $body
     *
     * @return EstimateMessageResponse
     */
    public function estimateMessage(array $body, ?RequestOptions $options = null): array
    {
        $this->assertRequiredBody('estimateMessage', $body);

        /** @var EstimateMessageResponse */
        return $this->call('estimateMessage', $options, body: $body);
    }

    /**
     * Relit un message par son identifiant — c'est ici que se lit le VERDICT de livraison.
     *
     * @return GetMessageResponse
     */
    public function getMessage(string $id, ?RequestOptions $options = null): array
    {
        /** @var GetMessageResponse */
        return $this->call('getMessage', $options, pathValues: ['id' => $id]);
    }

    /**
     * Parcourt le journal des messages du compte.
     *
     * @param ListMessagesQuery $query
     *
     * @return ListMessagesResponse
     */
    public function listMessages(array $query = [], ?RequestOptions $options = null): array
    {
        /** @var ListMessagesResponse */
        return $this->call('listMessages', $options, query: $query);
    }

    // ── Média ────────────────────────────────────────────────────────────────────────────────

    /**
     * Téléverse une pièce jointe et renvoie la référence à reposer dans `media.ref` d'un envoi.
     *
     * @return UploadMediaResponse
     */
    public function uploadMedia(MultipartUpload $upload, ?RequestOptions $options = null): array
    {
        if ($upload->fileName === '') {
            throw new RequestException(
                'senndo : fileName est requis — le serveur vérifie que le contenu correspond à '
                . 'l\'extension.',
            );
        }

        /** @var UploadMediaResponse */
        return $this->call('uploadMedia', $options, upload: $upload);
    }

    /**
     * Liste les fichiers du compte, l'espace occupé et la facturation du stockage.
     *
     * @param ListMediaQuery $query
     *
     * @return ListMediaResponse
     */
    public function listMedia(array $query = [], ?RequestOptions $options = null): array
    {
        /** @var ListMediaResponse */
        return $this->call('listMedia', $options, query: $query);
    }

    /**
     * Supprime un fichier. Répond 409 tant qu'un message ou une campagne le référence.
     */
    public function deleteMedia(string $id, ?RequestOptions $options = null): void
    {
        $this->call('deleteMedia', $options, pathValues: ['id' => $id]);
    }

    // ── Compte : solde, tarifs, devises, émetteurs ───────────────────────────────────────────

    /**
     * Le solde du compte, converti dans une devise ARMÉE.
     *
     * Aucun repli sur l'USD n'est effectué par le SDK quand la conversion échoue : la route refuse
     * précisément de commettre cette faute, et un SDK qui rattraperait le refus en rendant des
     * dollars afficherait un montant faux dans une devise que l'utilisateur croit être la sienne.
     *
     * @param GetBalanceQuery $query
     *
     * @return GetBalanceResponse
     */
    public function getBalance(array $query = [], ?RequestOptions $options = null): array
    {
        /** @var GetBalanceResponse */
        return $this->call('getBalance', $options, query: $query);
    }

    /**
     * Le catalogue des devises. `billable` distingue « convertible » de « encaissable ».
     *
     * @return ListCurrenciesResponse
     */
    public function listCurrencies(?RequestOptions $options = null): array
    {
        /** @var ListCurrenciesResponse */
        return $this->call('listCurrencies', $options);
    }

    /**
     * Les tarifs du compte.
     *
     * `costs` = ce que VOUS payez à votre fournisseur direct, `prices` = ce que VOUS facturez à vos
     * comptes enfants. Aucun coût plateforme, aucun tarif d'un compte voisin n'entre dans cette
     * réponse.
     *
     * @return ListPricesResponse
     */
    public function listPrices(?RequestOptions $options = null): array
    {
        /** @var ListPricesResponse */
        return $this->call('listPrices', $options);
    }

    /**
     * Les Sender IDs du compte, avec leur statut de cycle de vie ET leur approbation PAR PAYS.
     *
     * Proposer un émetteur sans lire `countries` conduit à un envoi refusé sur une destination où il
     * n'est pas approuvé.
     *
     * @return ListSenderIdsResponse
     */
    public function listSenderIds(?RequestOptions $options = null): array
    {
        /** @var ListSenderIdsResponse */
        return $this->call('listSenderIds', $options);
    }

    /**
     * Le grand livre du compte.
     *
     * La dépense NETTE d'un message est `billedAmountUsd − reversedAmountUsd` : sommer le brut
     * SURESTIME de tout ce qui a été contre-passé. Les montants sont des chaînes décimales —
     * additionnez-les avec `bcadd`, jamais avec l'opérateur `+`.
     *
     * @param ListLedgerQuery $query
     *
     * @return ListLedgerResponse
     */
    public function listLedger(array $query = [], ?RequestOptions $options = null): array
    {
        /** @var ListLedgerResponse */
        return $this->call('listLedger', $options, query: $query);
    }

    // ── Réception ────────────────────────────────────────────────────────────────────────────

    /**
     * Les conversations entrantes, la plus récente d'abord.
     *
     * @param ListInboxThreadsQuery $query
     *
     * @return ListInboxThreadsResponse
     */
    public function listInboxThreads(array $query = [], ?RequestOptions $options = null): array
    {
        /** @var ListInboxThreadsResponse */
        return $this->call('listInboxThreads', $options, query: $query);
    }

    /**
     * Les messages d'une conversation. `channel` et `contact` sont obligatoires.
     *
     * @param ListInboxMessagesQuery $query
     *
     * @return ListInboxMessagesResponse
     */
    public function listInboxMessages(array $query, ?RequestOptions $options = null): array
    {
        $this->assertRequiredQuery('listInboxMessages', $query);

        /** @var ListInboxMessagesResponse */
        return $this->call('listInboxMessages', $options, query: $query);
    }

    // ── WhatsApp ─────────────────────────────────────────────────────────────────────────────

    /**
     * Les modèles WhatsApp du compte, dont ceux partagés par la plateforme.
     *
     * @return ListWaTemplatesResponse
     */
    public function listWaTemplates(?RequestOptions $options = null): array
    {
        /** @var ListWaTemplatesResponse */
        return $this->call('listWaTemplates', $options);
    }

    /**
     * Les numéros WhatsApp Cloud rattachés au compte, et les émetteurs partagés disponibles.
     *
     * @return ListWaCloudNumbersResponse
     */
    public function listWaCloudNumbers(?RequestOptions $options = null): array
    {
        /** @var ListWaCloudNumbersResponse */
        return $this->call('listWaCloudNumbers', $options);
    }

    /**
     * Les identifiants d'acheminement apportés par le compte, et le repli partagé.
     *
     * Aucun secret n'en sort : le jeton n'est dans aucune réponse, et de l'identifiant de
     * compte seuls les quatre derniers caractères sont rendus.
     *
     * @return GetRoutingCredentialsResponse
     */
    public function getRoutingCredentials(?RequestOptions $options = null): array
    {
        /** @var GetRoutingCredentialsResponse */
        return $this->call('getRoutingCredentials', $options);
    }

    // ── Webhooks ─────────────────────────────────────────────────────────────────────────────

    /**
     * Les endpoints de webhook du compte, révoqués compris.
     *
     * @return ListWebhooksResponse
     */
    public function listWebhooks(?RequestOptions $options = null): array
    {
        /** @var ListWebhooksResponse */
        return $this->call('listWebhooks', $options);
    }

    /**
     * Crée un endpoint de webhook et renvoie son SECRET — la seule fois où il est lisible.
     *
     * CET APPEL N'EST JAMAIS RETENTÉ AUTOMATIQUEMENT. Il crée une ressource à chaque exécution :
     * une retentative aveugle produirait deux endpoints, donc DEUX livraisons pour chaque
     * événement. En cas d'échec de transport, listez avant de recréer.
     *
     * @param CreateWebhookBody $body
     *
     * @return CreateWebhookResponse
     */
    public function createWebhook(array $body, ?RequestOptions $options = null): array
    {
        $this->assertRequiredBody('createWebhook', $body);

        /** @var CreateWebhookResponse */
        return $this->call('createWebhook', $options, body: $body);
    }

    /**
     * Révoque un endpoint. Les livraisons cessent ; l'historique reste lisible.
     *
     * @return RevokeWebhookResponse
     */
    public function revokeWebhook(string $id, ?RequestOptions $options = null): array
    {
        /** @var RevokeWebhookResponse */
        return $this->call('revokeWebhook', $options, pathValues: ['id' => $id]);
    }

    /**
     * L'historique des livraisons de webhook, avec les compteurs d'état.
     *
     * @param ListWebhookDeliveriesQuery $query
     *
     * @return ListWebhookDeliveriesResponse
     */
    public function listWebhookDeliveries(array $query = [], ?RequestOptions $options = null): array
    {
        /** @var ListWebhookDeliveriesResponse */
        return $this->call('listWebhookDeliveries', $options, query: $query);
    }

    // ── Interne ──────────────────────────────────────────────────────────────────────────────

    /**
     * @param array<string, string> $pathValues
     * @param array<string, mixed>  $query
     * @param array<string, mixed>  $body
     *
     * @return array<string, mixed>|null
     */
    private function call(
        string $operationId,
        ?RequestOptions $options,
        array $pathValues = [],
        array $query = [],
        ?array $body = null,
        ?MultipartUpload $upload = null,
    ): ?array {
        // Normalisé ici plutôt qu'au point d'usage : `$options?->timeout ?? $this->timeout` est
        // redondant (`??` neutralise déjà l'accès sur null), et l'écrire trois fois invite à croire
        // que le `?->` protège quelque chose.
        $options ??= new RequestOptions();

        return Http::performRequest(
            transport: $this->transport,
            apiKey: $this->apiKey,
            baseUrl: $this->baseUrl,
            userAgent: $this->userAgent,
            timeout: $options->timeout ?? $this->timeout,
            maxRetries: $options->maxRetries ?? $this->maxRetries,
            descriptor: Contract::OPERATIONS[$operationId],
            pathValues: $pathValues,
            query: $query,
            body: $body,
            upload: $upload,
            extraHeaders: $options->headers,
        );
    }

    /**
     * Vérifie les champs obligatoires DEPUIS LE DESCRIPTEUR, jamais depuis une liste recopiée.
     *
     * Un champ ajouté au contrat devient obligatoire ici à la régénération, sans édition.
     *
     * @param array<string, mixed> $body
     */
    private function assertRequiredBody(string $operationId, array $body): void
    {
        foreach (Contract::OPERATIONS[$operationId]['requiredBodyFields'] as $field) {
            $value = $body[$field] ?? null;
            if ($value === null || $value === '') {
                throw new RequestException(sprintf(
                    'senndo : %s — le champ obligatoire « %s » est absent.',
                    $operationId,
                    $field,
                ));
            }
        }
    }

    /**
     * @param array<string, mixed> $query
     */
    private function assertRequiredQuery(string $operationId, array $query): void
    {
        foreach (Contract::OPERATIONS[$operationId]['requiredQueryParams'] as $name) {
            $value = $query[$name] ?? null;
            if ($value === null || $value === '') {
                throw new RequestException(sprintf(
                    'senndo : %s — le paramètre obligatoire « %s » est absent.',
                    $operationId,
                    $name,
                ));
            }
        }
    }
}
