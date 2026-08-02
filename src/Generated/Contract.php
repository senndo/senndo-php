<?php

declare(strict_types=1);

namespace Senndo\Generated;

/**
 * FICHIER GÉNÉRÉ — NE PAS ÉDITER À LA MAIN.
 *
 * Source : `OPENAPI_OPERATIONS` du monorepo senndo, document version 1.0.0.
 * Régénérer : `node packages/sdk-codegen/bin/generate.mjs php`.
 *
 * Toute édition manuelle est effacée à la prochaine génération, et le test de fraîcheur la signale
 * en ROUGE avant même qu'elle atteigne une revue.
 *
 * LES MONTANTS SONT DES CHAÎNES DÉCIMALES, jamais des flottants. Un `NUMERIC(18,6)` passé par un
 * double IEEE-754 perd des unités sur les longues traînes, et un prix unitaire sub-centime arrondi
 * à deux décimales devient zéro. Additionnez-les avec `bcadd` ou une bibliothèque décimale,
 * jamais avec l'opérateur `+`.
 *
 * @phpstan-type SendMessageBodyMedia array{
 *     ref?: string,
 * }
 *
 * @phpstan-type SendMessageBodyTemplate array{
 *     name?: string,
 *     language?: string,
 *     id?: string,
 *     variables?: list<string>,
 *     headerVariable?: string,
 *     urlButtonVariable?: string,
 * }
 *
 * @phpstan-type SendMessageBody array{
 *     channel: 'sms'|'whatsapp_cloud'|'whatsapp_baileys'|'email'|'voice',
 *     to: string,
 *     text?: string,
 *     media?: SendMessageBodyMedia,
 *     template?: SendMessageBodyTemplate,
 *     idempotencyKey: string,
 *     senderId?: string,
 *     country?: string,
 *     category?: 'marketing'|'utility'|'authentication'|'service',
 *     tier?: 'standard'|'premium',
 *     subject?: string,
 * }
 *
 * @phpstan-type SendMessageResponse array{
 *     id: string,
 *     status: 'pending'|'dispatching'|'queued'|'sent'|'delivered'|'read'|'failed',
 *     channel: 'sms'|'whatsapp_cloud'|'whatsapp_baileys'|'email'|'voice',
 *     to: string,
 *     senderId?: string|null,
 *     routeRuleId?: string|null,
 *     billedAmountUsd?: string|null,
 *     billedCurrency?: string|null,
 *     replay: bool,
 * }
 *
 * @phpstan-type GetMessageResponse array{
 *     id: string,
 *     createdAt: string,
 *     channel: 'sms'|'whatsapp_cloud'|'whatsapp_baileys'|'email'|'voice',
 *     toAddr?: string|null,
 *     senderId?: string|null,
 *     status: 'pending'|'dispatching'|'queued'|'sent'|'delivered'|'read'|'failed',
 *     billedAmountUsd?: string|null,
 *     reversedAmountUsd?: string|null,
 *     failureCode?: string|null,
 *     billedCurrency?: string|null,
 *     category?: string|null,
 *     body?: string,
 *     source?: 'console'|'api'|'api_test',
 * }
 *
 * @phpstan-type ListMessagesQuery array{
 *     page?: int,
 *     pageSize?: int,
 *     sort?: 'date'|'channel'|'status'|'amount',
 *     dir?: 'asc'|'desc',
 *     channel?: 'sms'|'whatsapp_cloud'|'whatsapp_baileys'|'email'|'voice',
 *     status?: 'pending'|'dispatching'|'queued'|'sent'|'delivered'|'read'|'failed',
 *     from?: string,
 *     to?: string,
 *     via?: 'api',
 * }
 *
 * @phpstan-type ListMessagesResponseRowsItem array{
 *     id: string,
 *     createdAt: string,
 *     channel: 'sms'|'whatsapp_cloud'|'whatsapp_baileys'|'email'|'voice',
 *     toAddr?: string|null,
 *     senderId?: string|null,
 *     status: 'pending'|'dispatching'|'queued'|'sent'|'delivered'|'read'|'failed',
 *     billedAmountUsd?: string|null,
 *     reversedAmountUsd?: string|null,
 *     failureCode?: string|null,
 *     billedCurrency?: string|null,
 *     category?: string|null,
 *     body?: string,
 *     source?: 'console'|'api'|'api_test',
 * }
 *
 * @phpstan-type ListMessagesResponse array{
 *     page: int,
 *     pageSize: int,
 *     total: int,
 *     scope: 'account'|'network',
 *     rows: list<ListMessagesResponseRowsItem>,
 * }
 *
 * @phpstan-type UploadMediaResponse array{
 *     ref: string,
 *     kind: 'image'|'video'|'audio'|'document',
 *     fileName: string,
 *     mime: string,
 *     sizeBytes: int,
 * }
 *
 * @phpstan-type ListMediaQuery array{
 *     page?: int,
 *     pageSize?: int,
 * }
 *
 * @phpstan-type ListMediaResponseMediaItem array{
 *     ref: string,
 *     kind: 'image'|'video'|'audio'|'document',
 *     mime: string,
 *     fileName: string,
 *     sizeBytes: int,
 *     createdAt: string,
 *     inUse: bool,
 *     previewUrl?: string|null,
 * }
 *
 * @phpstan-type ListMediaResponseUsage array{
 *     usedBytes: int,
 *     quotaBytes: int,
 * }
 *
 * @phpstan-type ListMediaResponseBillingCycle array{
 *     period: string,
 *     status: string,
 *     measuredBytes: int,
 *     tranches: int,
 *     amountUsd: string,
 *     updatedAt?: string|null,
 * }
 *
 * @phpstan-type ListMediaResponseBilling array{
 *     pricePerGibUsd: string|null,
 *     nextPeriodStart: string,
 *     cycle?: ListMediaResponseBillingCycle|null,
 * }
 *
 * @phpstan-type ListMediaResponse array{
 *     media: list<ListMediaResponseMediaItem>,
 *     page: int,
 *     pageSize: int,
 *     total: int,
 *     usage: ListMediaResponseUsage,
 *     billing: ListMediaResponseBilling,
 * }
 *
 * @phpstan-type ListPricesResponseCostsItem array{
 *     channel: string,
 *     destGroup: string,
 *     priceUsd: string,
 * }
 *
 * @phpstan-type ListPricesResponsePricesItem array{
 *     channel: string,
 *     destGroup: string,
 *     priceUsd: string,
 *     buyerAccountId?: string|null,
 * }
 *
 * @phpstan-type ListPricesResponse array{
 *     accountId: string,
 *     costs: list<ListPricesResponseCostsItem>,
 *     prices: list<ListPricesResponsePricesItem>,
 * }
 *
 * @phpstan-type GetBalanceQuery array{
 *     currency?: string,
 * }
 *
 * @phpstan-type GetBalanceResponse array{
 *     accountId: string,
 *     balanceUsd: string,
 *     currency: string,
 *     unitsPerUsd: string,
 *     balance: string,
 *     billable: bool,
 * }
 *
 * @phpstan-type ListCurrenciesResponseCurrenciesItem array{
 *     currency: string,
 *     unitsPerUsd: string,
 *     billable: bool,
 * }
 *
 * @phpstan-type ListCurrenciesResponse array{
 *     currencies: list<ListCurrenciesResponseCurrenciesItem>,
 * }
 *
 * @phpstan-type ListSenderIdsResponseSenderIdsItemCountriesItem array{
 *     country: string,
 *     status: 'approved'|'pending'|'rejected',
 * }
 *
 * @phpstan-type ListSenderIdsResponseSenderIdsItem array{
 *     id: string,
 *     value: string,
 *     channel: string,
 *     shared: bool,
 *     lifecycleStatus: 'active'|'suspended'|'archived',
 *     countries: list<ListSenderIdsResponseSenderIdsItemCountriesItem>,
 * }
 *
 * @phpstan-type ListSenderIdsResponse array{
 *     senderIds: list<ListSenderIdsResponseSenderIdsItem>,
 * }
 *
 * @phpstan-type EstimateMessageBody array{
 *     channel: 'sms'|'whatsapp_cloud'|'whatsapp_baileys'|'email'|'voice',
 *     text: string,
 *     recipients: int,
 *     senderId?: string|null,
 *     country?: string|null,
 *     hasAttachment?: bool,
 * }
 *
 * @phpstan-type EstimateMessageResponse array{
 *     channel: string,
 *     destGroup: string,
 *     encoding: 'gsm7'|'unicode',
 *     chars: int,
 *     segments: int,
 *     units: int,
 *     recipients: int,
 *     transliterateGsm7?: bool,
 *     transliterated?: bool,
 *     unitPriceUsd: string,
 *     totalUsd: string,
 * }
 *
 * @phpstan-type ListLedgerQuery array{
 *     page?: int,
 *     pageSize?: int,
 *     kind?: string,
 *     from?: string,
 *     to?: string,
 * }
 *
 * @phpstan-type ListLedgerResponseRowsItem array{
 *     id: string,
 *     createdAt: string,
 *     kind: string,
 *     amountUsd: string,
 *     balanceAfter?: string|null,
 *     channel?: string|null,
 *     toAddr?: string|null,
 *     status?: string|null,
 * }
 *
 * @phpstan-type ListLedgerResponseAggregates array{
 *     debitUsd: string,
 *     creditUsd: string,
 *     closingBalanceUsd?: string|null,
 * }
 *
 * @phpstan-type ListLedgerResponse array{
 *     page: int,
 *     pageSize: int,
 *     total: int,
 *     rows: list<ListLedgerResponseRowsItem>,
 *     aggregates: ListLedgerResponseAggregates,
 * }
 *
 * @phpstan-type ListInboxThreadsQuery array{
 *     page?: int,
 *     pageSize?: int,
 * }
 *
 * @phpstan-type ListInboxThreadsResponseThreadsItem array{
 *     channel: string,
 *     contact: string,
 *     lastBody: string,
 *     lastAt: string,
 * }
 *
 * @phpstan-type ListInboxThreadsResponse array{
 *     page: int,
 *     pageSize: int,
 *     threads: list<ListInboxThreadsResponseThreadsItem>,
 * }
 *
 * @phpstan-type ListInboxMessagesQuery array{
 *     channel: string,
 *     contact: string,
 *     page?: int,
 *     pageSize?: int,
 * }
 *
 * @phpstan-type ListInboxMessagesResponseMessagesItem array{
 *     id: string,
 *     channel: string,
 *     direction: 'in'|'out',
 *     fromAddr?: string|null,
 *     toAddr: string,
 *     body: string,
 *     createdAt: string,
 * }
 *
 * @phpstan-type ListInboxMessagesResponse array{
 *     page: int,
 *     pageSize: int,
 *     messages: list<ListInboxMessagesResponseMessagesItem>,
 * }
 *
 * @phpstan-type ListWaTemplatesResponseTemplatesItem array{
 *     id: string,
 *     name: string,
 *     language: string,
 *     category: 'MARKETING'|'UTILITY'|'AUTHENTICATION',
 *     status: 'draft'|'pending'|'approved'|'rejected'|'paused',
 *     platformShared: bool,
 *     body: string,
 *     footer?: string,
 * }
 *
 * @phpstan-type ListWaTemplatesResponse array{
 *     templates: list<ListWaTemplatesResponseTemplatesItem>,
 * }
 *
 * @phpstan-type ListWaCloudNumbersResponseNumbersItem array{
 *     id: string,
 *     phoneNumberId: string,
 *     displayNumber?: string|null,
 *     hasToken: bool,
 *     createdAt: string,
 * }
 *
 * @phpstan-type ListWaCloudNumbersResponseSharedSendersItem array{
 *     channel: string,
 *     verifiedName?: string|null,
 *     pairedNumber?: string|null,
 * }
 *
 * @phpstan-type ListWaCloudNumbersResponse array{
 *     numbers: list<ListWaCloudNumbersResponseNumbersItem>,
 *     platformFallbackAvailable: bool,
 *     sharedSenders: list<ListWaCloudNumbersResponseSharedSendersItem>,
 * }
 *
 * @phpstan-type ListWebhooksResponseEndpointsItem array{
 *     id: string,
 *     name: string,
 *     url: string,
 *     events: list<string>,
 *     revokedAt?: string|null,
 *     createdAt: string,
 * }
 *
 * @phpstan-type ListWebhooksResponse array{
 *     endpoints: list<ListWebhooksResponseEndpointsItem>,
 * }
 *
 * @phpstan-type CreateWebhookBody array{
 *     name: string,
 *     url: string,
 *     events: list<'message.sent'|'message.failed'|'message.inbound'>,
 * }
 *
 * @phpstan-type CreateWebhookResponse array{
 *     id: string,
 *     name: string,
 *     url: string,
 *     events: list<string>,
 *     createdAt: string,
 *     secret: string,
 * }
 *
 * @phpstan-type RevokeWebhookResponse array{
 *     id: string,
 *     revoked: bool,
 * }
 *
 * @phpstan-type ListWebhookDeliveriesQuery array{
 *     page?: int,
 *     pageSize?: int,
 *     status?: string,
 * }
 *
 * @phpstan-type ListWebhookDeliveriesResponseAggregates array{
 *     succeeded: int,
 *     failedPermanent: int,
 *     inFlight: int,
 * }
 *
 * @phpstan-type ListWebhookDeliveriesResponseDeliveriesItem array{
 *     id: string,
 *     endpointId: string,
 *     eventType: string,
 *     status: string,
 *     attempt: int,
 *     httpStatus?: int|null,
 *     error?: string|null,
 *     createdAt: string,
 * }
 *
 * @phpstan-type ListWebhookDeliveriesResponse array{
 *     page: int,
 *     pageSize: int,
 *     total: int,
 *     aggregates: ListWebhookDeliveriesResponseAggregates,
 *     deliveries: list<ListWebhookDeliveriesResponseDeliveriesItem>,
 * }
 */
final class Contract
{
    /** Base publique de production. Surchargeable à la construction du client. */
    public const API_BASE_URL = 'https://api.senndo.com';

    /** Version du document OpenAPI dont ce fichier est dérivé. */
    public const CONTRACT_VERSION = '1.0.0';

    /** @var list<string> */
    public const CHANNELS = ['sms', 'whatsapp_cloud', 'whatsapp_baileys', 'email', 'voice'];

    /** @var list<string> */
    public const MESSAGE_STATUSES = ['pending', 'dispatching', 'queued', 'sent', 'delivered', 'read', 'failed'];

    /**
     * Les valeurs de code d'échec connues de CETTE version du SDK.
     *
     * LA LISTE N'EST PAS EXHAUSTIVE PAR CONSTRUCTION : senndo s'engage à AJOUTER des valeurs,
     * jamais à en retirer. Utilisez-la pour distinguer « code connu » de « code plus récent que
     * votre version du SDK » — jamais pour rejeter un code absent.
     *
     * @var list<string>
     */
    public const KNOWN_FAILURE_CODES = [
        'RECIPIENT_NOT_REACHABLE',
        'RECIPIENT_OPTED_OUT',
        'WHATSAPP_WINDOW_CLOSED',
        'TEMPLATE_INVALID',
        'SENDER_NOT_AUTHORIZED',
        'MESSAGE_BLOCKED',
        'UNSUPPORTED_CONTENT',
        'MEDIA_ERROR',
        'RATE_LIMITED',
        'NO_ANSWER',
        'BUSY',
        'CALL_CANCELED',
        'PROVIDER_REFUSED',
    ];

    /**
     * LA surface publique, dérivée du contrat. Une opération absente d'ici n'existe pas.
     *
     * @var array<string, array{
     *     operationId: string,
     *     methodName: string,
     *     method: string,
     *     path: string,
     *     pathParams: list<string>,
     *     queryParams: list<string>,
     *     requiredQueryParams: list<string>,
     *     requiredBodyFields: list<string>,
     *     contentType: string|null,
     *     successStatus: string,
     *     billableSideEffect: bool,
     * }>
     */
    public const OPERATIONS = [
        'sendMessage' => [
            'operationId' => 'sendMessage',
            'methodName' => 'sendMessage',
            'method' => 'POST',
            'path' => '/v1/messages',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => ['channel', 'to', 'idempotencyKey'],
            'contentType' => 'application/json',
            'successStatus' => '200',
            'billableSideEffect' => true,
        ],
        'getMessage' => [
            'operationId' => 'getMessage',
            'methodName' => 'getMessage',
            'method' => 'GET',
            'path' => '/v1/messages/{id}',
            'pathParams' => ['id'],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listMessages' => [
            'operationId' => 'listMessages',
            'methodName' => 'listMessages',
            'method' => 'GET',
            'path' => '/v1/messages',
            'pathParams' => [],
            'queryParams' => ['page', 'pageSize', 'sort', 'dir', 'channel', 'status', 'from', 'to', 'via'],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'uploadMedia' => [
            'operationId' => 'uploadMedia',
            'methodName' => 'uploadMedia',
            'method' => 'POST',
            'path' => '/v1/wa-media',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => ['file'],
            'contentType' => 'multipart/form-data',
            'successStatus' => '201',
            'billableSideEffect' => false,
        ],
        'listMedia' => [
            'operationId' => 'listMedia',
            'methodName' => 'listMedia',
            'method' => 'GET',
            'path' => '/v1/wa-media',
            'pathParams' => [],
            'queryParams' => ['page', 'pageSize'],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'deleteMedia' => [
            'operationId' => 'deleteMedia',
            'methodName' => 'deleteMedia',
            'method' => 'DELETE',
            'path' => '/v1/wa-media/{id}',
            'pathParams' => ['id'],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '204',
            'billableSideEffect' => false,
        ],
        'listPrices' => [
            'operationId' => 'listPrices',
            'methodName' => 'listPrices',
            'method' => 'GET',
            'path' => '/v1/prices',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'getBalance' => [
            'operationId' => 'getBalance',
            'methodName' => 'getBalance',
            'method' => 'GET',
            'path' => '/v1/balance',
            'pathParams' => [],
            'queryParams' => ['currency'],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listCurrencies' => [
            'operationId' => 'listCurrencies',
            'methodName' => 'listCurrencies',
            'method' => 'GET',
            'path' => '/v1/currencies',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listSenderIds' => [
            'operationId' => 'listSenderIds',
            'methodName' => 'listSenderIds',
            'method' => 'GET',
            'path' => '/v1/sender-ids',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'estimateMessage' => [
            'operationId' => 'estimateMessage',
            'methodName' => 'estimateMessage',
            'method' => 'POST',
            'path' => '/v1/messages/estimate',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => ['channel', 'text', 'recipients'],
            'contentType' => 'application/json',
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listLedger' => [
            'operationId' => 'listLedger',
            'methodName' => 'listLedger',
            'method' => 'GET',
            'path' => '/v1/ledger',
            'pathParams' => [],
            'queryParams' => ['page', 'pageSize', 'kind', 'from', 'to'],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listInboxThreads' => [
            'operationId' => 'listInboxThreads',
            'methodName' => 'listInboxThreads',
            'method' => 'GET',
            'path' => '/v1/inbox/threads',
            'pathParams' => [],
            'queryParams' => ['page', 'pageSize'],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listInboxMessages' => [
            'operationId' => 'listInboxMessages',
            'methodName' => 'listInboxMessages',
            'method' => 'GET',
            'path' => '/v1/inbox/messages',
            'pathParams' => [],
            'queryParams' => ['channel', 'contact', 'page', 'pageSize'],
            'requiredQueryParams' => ['channel', 'contact'],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listWaTemplates' => [
            'operationId' => 'listWaTemplates',
            'methodName' => 'listWaTemplates',
            'method' => 'GET',
            'path' => '/v1/wa-templates',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listWaCloudNumbers' => [
            'operationId' => 'listWaCloudNumbers',
            'methodName' => 'listWaCloudNumbers',
            'method' => 'GET',
            'path' => '/v1/wa-cloud/numbers',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listWebhooks' => [
            'operationId' => 'listWebhooks',
            'methodName' => 'listWebhooks',
            'method' => 'GET',
            'path' => '/v1/webhooks',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'createWebhook' => [
            'operationId' => 'createWebhook',
            'methodName' => 'createWebhook',
            'method' => 'POST',
            'path' => '/v1/webhooks',
            'pathParams' => [],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => ['name', 'url', 'events'],
            'contentType' => 'application/json',
            'successStatus' => '201',
            'billableSideEffect' => false,
        ],
        'revokeWebhook' => [
            'operationId' => 'revokeWebhook',
            'methodName' => 'revokeWebhook',
            'method' => 'POST',
            'path' => '/v1/webhooks/{id}/revoke',
            'pathParams' => ['id'],
            'queryParams' => [],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
        'listWebhookDeliveries' => [
            'operationId' => 'listWebhookDeliveries',
            'methodName' => 'listWebhookDeliveries',
            'method' => 'GET',
            'path' => '/v1/webhooks/deliveries',
            'pathParams' => [],
            'queryParams' => ['page', 'pageSize', 'status'],
            'requiredQueryParams' => [],
            'requiredBodyFields' => [],
            'contentType' => null,
            'successStatus' => '200',
            'billableSideEffect' => false,
        ],
    ];

    /** @var list<string> */
    public const OPERATION_IDS = [
        'sendMessage',
        'getMessage',
        'listMessages',
        'uploadMedia',
        'listMedia',
        'deleteMedia',
        'listPrices',
        'getBalance',
        'listCurrencies',
        'listSenderIds',
        'estimateMessage',
        'listLedger',
        'listInboxThreads',
        'listInboxMessages',
        'listWaTemplates',
        'listWaCloudNumbers',
        'listWebhooks',
        'createWebhook',
        'revokeWebhook',
        'listWebhookDeliveries',
    ];
}
