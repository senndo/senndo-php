<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 403 — la clé était valide, l'appel est refusé (allowlist, suspension, contenu bloqué).
 */
final class ForbiddenException extends ApiException
{
}
