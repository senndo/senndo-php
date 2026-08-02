<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 404 — la ressource n'existe pas, ou n'appartient pas au compte appelant.
 */
final class NotFoundException extends ApiException
{
}
