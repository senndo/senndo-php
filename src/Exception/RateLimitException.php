<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 429 — cadence dépassée. `retryAfter` porte l'attente demandée quand elle est connue.
 *
 * DEUX CODES, DEUX REMÈDES, et c'est sur `code` qu'on les sépare — jamais sur le statut :
 *
 *  - `VELOCITY_EXCEEDED` — trop d'envois pour ce compte sur 60 secondes. Étalez la campagne ;
 *    le message refusé n'a jamais été débité.
 *  - `RATE_LIMITED` — trop d'appels (ou trop d'octets) pour cette clé sur 60 secondes, lectures
 *    comprises. Espacez les requêtes, ou parallélisez moins.
 */
final class RateLimitException extends ApiException
{
}
