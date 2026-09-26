<?php

declare(strict_types=1);

namespace Senndo\Generated;

use Senndo\MultipartUpload;
use Senndo\RequestOptions;

/**
 * FICHIER GÉNÉRÉ — NE PAS ÉDITER À LA MAIN.
 *
 * LA CONFORMITÉ DU CLIENT PHP AU CONTRAT EST STRUCTURELLE, PAS TESTÉE. `Senndo\Client`
 * implémente cette interface : si une opération du contrat n'a pas sa méthode, ou si une signature
 * diverge, PHP refuse de charger la classe. Aucune assertion à se souvenir de lancer.
 *
 * Régénérer : `node packages/sdk-codegen/bin/generate.mjs php`.
 *
 * @phpstan-import-type CheckVerificationBody from Contract
 * @phpstan-import-type CheckVerificationResponse from Contract
 * @phpstan-import-type CreateVerificationBody from Contract
 * @phpstan-import-type CreateVerificationResponse from Contract
 * @phpstan-import-type CreateWebhookBody from Contract
 * @phpstan-import-type CreateWebhookResponse from Contract
 * @phpstan-import-type EstimateMessageBody from Contract
 * @phpstan-import-type EstimateMessageResponse from Contract
 * @phpstan-import-type GetBalanceQuery from Contract
 * @phpstan-import-type GetBalanceResponse from Contract
 * @phpstan-import-type GetMessageResponse from Contract
 * @phpstan-import-type GetRoutingCredentialsResponse from Contract
 * @phpstan-import-type GetVerificationResponse from Contract
 * @phpstan-import-type ListContentTemplatesResponse from Contract
 * @phpstan-import-type ListCurrenciesResponse from Contract
 * @phpstan-import-type ListInboxMessagesQuery from Contract
 * @phpstan-import-type ListInboxMessagesResponse from Contract
 * @phpstan-import-type ListInboxThreadsQuery from Contract
 * @phpstan-import-type ListInboxThreadsResponse from Contract
 * @phpstan-import-type ListLedgerQuery from Contract
 * @phpstan-import-type ListLedgerResponse from Contract
 * @phpstan-import-type ListMediaQuery from Contract
 * @phpstan-import-type ListMediaResponse from Contract
 * @phpstan-import-type ListMessagesQuery from Contract
 * @phpstan-import-type ListMessagesResponse from Contract
 * @phpstan-import-type ListPricesResponse from Contract
 * @phpstan-import-type ListSenderIdsResponse from Contract
 * @phpstan-import-type ListWaCloudNumbersResponse from Contract
 * @phpstan-import-type ListWaTemplatesResponse from Contract
 * @phpstan-import-type ListWebhookDeliveriesQuery from Contract
 * @phpstan-import-type ListWebhookDeliveriesResponse from Contract
 * @phpstan-import-type ListWebhooksResponse from Contract
 * @phpstan-import-type RevokeWebhookResponse from Contract
 * @phpstan-import-type SendMessageBody from Contract
 * @phpstan-import-type SendMessageResponse from Contract
 * @phpstan-import-type UploadMediaResponse from Contract
 */
interface ClientContract
{
    /**
     * Envoyer un message
     *
     * @param SendMessageBody $body
     * @return SendMessageResponse
     */
    public function sendMessage(array $body, ?RequestOptions $options = null): array;

    /**
     * Lire le statut d’un message
     *
     * @param string $id
     * @return GetMessageResponse
     */
    public function getMessage(string $id, ?RequestOptions $options = null): array;

    /**
     * Envoyer un code de vérification
     *
     * @param CreateVerificationBody $body
     * @return CreateVerificationResponse
     */
    public function createVerification(array $body, ?RequestOptions $options = null): array;

    /**
     * Contrôler un code de vérification
     *
     * @param CheckVerificationBody $body
     * @return CheckVerificationResponse
     */
    public function checkVerification(array $body, ?RequestOptions $options = null): array;

    /**
     * Lire l’état d’une vérification
     *
     * @param string $id
     * @return GetVerificationResponse
     */
    public function getVerification(string $id, ?RequestOptions $options = null): array;

    /**
     * Lister les messages envoyés
     *
     * @param ListMessagesQuery $query
     * @return ListMessagesResponse
     */
    public function listMessages(array $query = [], ?RequestOptions $options = null): array;

    /**
     * Téléverser une pièce jointe
     *
     * @param MultipartUpload $upload
     * @return UploadMediaResponse
     */
    public function uploadMedia(MultipartUpload $upload, ?RequestOptions $options = null): array;

    /**
     * Lister ses fichiers
     *
     * @param ListMediaQuery $query
     * @return ListMediaResponse
     */
    public function listMedia(array $query = [], ?RequestOptions $options = null): array;

    /**
     * Supprimer un fichier
     *
     * @param string $id
     * @return void
     */
    public function deleteMedia(string $id, ?RequestOptions $options = null): void;

    /**
     * Consulter ses tarifs
     *
     * @return ListPricesResponse
     */
    public function listPrices(?RequestOptions $options = null): array;

    /**
     * Consulter son solde
     *
     * @param GetBalanceQuery $query
     * @return GetBalanceResponse
     */
    public function getBalance(array $query = [], ?RequestOptions $options = null): array;

    /**
     * Lister les devises servies
     *
     * @return ListCurrenciesResponse
     */
    public function listCurrencies(?RequestOptions $options = null): array;

    /**
     * Lister ses expéditeurs
     *
     * @return ListSenderIdsResponse
     */
    public function listSenderIds(?RequestOptions $options = null): array;

    /**
     * Estimer le coût d’un envoi
     *
     * @param EstimateMessageBody $body
     * @return EstimateMessageResponse
     */
    public function estimateMessage(array $body, ?RequestOptions $options = null): array;

    /**
     * Lire son grand livre
     *
     * @param ListLedgerQuery $query
     * @return ListLedgerResponse
     */
    public function listLedger(array $query = [], ?RequestOptions $options = null): array;

    /**
     * Lister les conversations entrantes
     *
     * @param ListInboxThreadsQuery $query
     * @return ListInboxThreadsResponse
     */
    public function listInboxThreads(array $query = [], ?RequestOptions $options = null): array;

    /**
     * Lire une conversation
     *
     * @param ListInboxMessagesQuery $query
     * @return ListInboxMessagesResponse
     */
    public function listInboxMessages(array $query, ?RequestOptions $options = null): array;

    /**
     * Lister ses modèles WhatsApp
     *
     * @return ListWaTemplatesResponse
     */
    public function listWaTemplates(?RequestOptions $options = null): array;

    /**
     * Lister ses émetteurs WhatsApp
     *
     * @return ListWaCloudNumbersResponse
     */
    public function listWaCloudNumbers(?RequestOptions $options = null): array;

    /**
     * Connaître ses identifiants d’acheminement
     *
     * @return GetRoutingCredentialsResponse
     */
    public function getRoutingCredentials(?RequestOptions $options = null): array;

    /**
     * Lister les modèles hébergés disponibles
     *
     * @return ListContentTemplatesResponse
     */
    public function listContentTemplates(?RequestOptions $options = null): array;

    /**
     * Lister ses webhooks
     *
     * @return ListWebhooksResponse
     */
    public function listWebhooks(?RequestOptions $options = null): array;

    /**
     * Enregistrer un webhook
     *
     * @param CreateWebhookBody $body
     * @return CreateWebhookResponse
     */
    public function createWebhook(array $body, ?RequestOptions $options = null): array;

    /**
     * Révoquer un webhook
     *
     * @param string $id
     * @return RevokeWebhookResponse
     */
    public function revokeWebhook(string $id, ?RequestOptions $options = null): array;

    /**
     * Journal des livraisons de webhooks
     *
     * @param ListWebhookDeliveriesQuery $query
     * @return ListWebhookDeliveriesResponse
     */
    public function listWebhookDeliveries(array $query = [], ?RequestOptions $options = null): array;
}
