# Journal des versions — `senndo/senndo-php`

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et le versionnage
sémantique.

## 0.1.2 — 2026-08-02

Quatre champs de plus, trouvés non par une sonde mais par un **gate statique** : la campagne live
de `0.1.1` n'observait que ce que l'état courant du serveur produisait, et l'observé n'est qu'une
borne inférieure de l'écart. Le gate, lui, compare les types de TOUTES les réponses aux schémas du
contrat, sans réseau et sans compte.

### Corrigé

- `createWebhook.revokedAt` — toujours `null` à la création, mais présent : la réponse de création
  a donc la MÊME forme qu'une ligne de `listWebhooks`, et se range dans une liste sans cas
  particulier. L'exemple du contrat le portait déjà ; le schéma, non.
- `listWebhookDeliveries.deliveries[]` — `durationMs` (ce qui distingue « votre serveur a refusé »
  de « votre serveur n'a pas répondu à temps »), `testMode` (un endpoint reçoit les événements de
  test ET de production : c'est ce drapeau qui les sépare) et `deliveredAt` (horodatage de l'issue
  terminale).

### Note

`0.1.1` n'a atteint que npm et PyPI ; Packagist ne l'a jamais vue. `0.1.2` atterrit sur les trois
registres ensemble — un paquet PHP qui passe de `0.1.0` à `0.1.2` ne saute donc rien.

## 0.1.1 — 2026-08-02

Première confrontation des trois SDK à l'API **réelle** (`scripts/sdk-live/`). Les gates
prouvaient la fidélité de l'émetteur au contrat et le comportement du transport face à un serveur
simulé ; aucun ne prouvait que le SERVEUR répond ce que le contrat annonce. Il ne le faisait pas
partout.

### Corrigé

- **Plus de notice de dépréciation sur PHP 8.5.** `CurlTransport` appelait `curl_close()`, sans
  effet depuis PHP 8.0 et déprécié en 8.5 : l'application du client voyait un avertissement à
  CHAQUE requête. Aucun gate ne pouvait le voir — les tests injectent un transport de substitution
  et ne touchent jamais cURL. C'est l'exécution du paquet PUBLIÉ, depuis un répertoire vierge, qui
  l'a révélé.
- **21 champs que l'API renvoie et que le contrat ne décrivait pas** sont désormais dans les
  formes PHPStan — donc vérifiés au niveau max au lieu d'être invisibles au client.
  `listSenderIds` (`canReview`, `ownerAccountId`, `ownerName`, `verification`, `createdAt`,
  `suspensionReason`, `suspendedAt`, `archivedAt`), `listWaTemplates` (`requestedCategory`,
  `effectiveCategory`, `rejectionReason`, `quality`, `source`, `header`, `bodyExamples`,
  `buttons`, `createdAt`, `updatedAt`), `listWaCloudNumbers` → `sharedSenders` (`kind`, `oneWay`,
  `sessionId`), `listLedger` → `rows` (`receiptRef`).

  Deux d'entre eux valaient à eux seuls la version : `buttons` est ce qui dit qu'un modèle attend
  un CODE à l'envoi — l'omettre fait échouer l'envoi APRÈS le débit ; `effectiveCategory` est la
  catégorie que Meta a réellement retenue, et un modèle demandé en UTILITY puis reclassé en
  MARKETING ne coûte pas le même prix.

### Note

Aucun écart de contrat n'était propre à un SDK : les trois recevaient exactement les mêmes champs
non documentés. La génération faisait son travail ; c'est la source qui était incomplète. La
dépréciation cURL, elle, ne concernait que PHP.

## 0.1.0 — 2026-08-02

Première publication. La version reste `0.x` tant que la surface n'a pas été exercée par des
intégrations réelles : un `1.0.0` promet une stabilité que rien n'a encore éprouvée.

### Ajouté

- `Senndo\Client` — les 20 opérations de l'API publique, une méthode par opération, nommées comme
  les identifiants du contrat.
- Conformité **structurelle** au contrat : `Client` implémente `Senndo\Generated\ClientContract`,
  une interface générée. Une opération sans méthode, ou une signature divergente, empêche PHP de
  charger la classe.
- Types générés depuis le contrat OpenAPI de senndo, en *array shapes* PHPStan : corps, paramètres
  de requête et réponses, exercés au niveau maximal.
- Exceptions typées par famille (`InsufficientFundsException`, `RateLimitException`,
  `ValidationException`…) — on branche sur une classe ou sur `$erreur->errorCode`, jamais sur un
  message. Le code stable vit sur `errorCode` et non sur `code`, que `Exception` possède déjà en PHP.
- Idempotence de première classe : `idempotencyKey` obligatoire sur l'envoi, préfixes réservés
  refusés localement, `Http::newIdempotencyKey()` pour les cas sans clé métier.
- Retentatives limitées à ce qui est rejouable : `GET`/`DELETE` et les `POST` porteurs d'une clé
  d'idempotence, sur échec de transport, 429 et 5xx uniquement.
- Délais explicites (30 s par défaut), surchargeables par appel via `RequestOptions`.
- Transport injectable (`Senndo\Transport`) — cURL par défaut, aucune dépendance Composer.
- Clé API masquée dans `__toString()` et `__debugInfo()`.
