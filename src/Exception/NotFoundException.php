<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 404 — la ressource n'existe pas, ou n'appartient pas au compte appelant.
 *
 * `ROUTE_NOT_FOUND` est le cas à part : aucune route ne sert ce couple méthode + chemin. C'est
 * une URL fautive, pas une ressource absente.
 */
final class NotFoundException extends ApiException
{
}
