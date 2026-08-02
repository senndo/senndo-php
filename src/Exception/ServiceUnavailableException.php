<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 503 — le canal ou le service ne peut pas livrer MAINTENANT.
 *
 * Ce refus arrive AVANT tout débit (un canal qui ne peut pas livrer refuse avant de facturer).
 * Rien n'a été prélevé.
 */
final class ServiceUnavailableException extends ApiException
{
}
