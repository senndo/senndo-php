<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 413 — le corps de la requête dépasse le plafond accepté.
 *
 * Deux causes distinctes : un FICHIER trop volumineux sur un envoi multipart
 * (`FILE_TOO_LARGE`), ou un CORPS JSON au-delà du plafond de la route
 * (`BODY_TOO_LARGE`, 1 Mio par défaut). Le code du corps d'erreur les sépare — la
 * taille du fichier et celle de la requête ne se corrigent pas de la même façon.
 */
final class PayloadTooLargeException extends ApiException
{
}
