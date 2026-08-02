<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 400 / 422 — la requête est mal formée, ou irrecevable en l'état. Corrigez l'appel.
 */
final class ValidationException extends ApiException
{
}
