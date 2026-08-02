<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 409 — conflit d'état : média encore référencé, quota dépassé, mode d'idempotence divergent.
 */
final class ConflictException extends ApiException
{
}
