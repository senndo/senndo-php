<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 429 — cadence dépassée. `retryAfter` porte l'attente demandée quand elle est connue.
 */
final class RateLimitException extends ApiException
{
}
