# Journal des versions — `senndo/senndo-php`

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et le versionnage
sémantique.

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
