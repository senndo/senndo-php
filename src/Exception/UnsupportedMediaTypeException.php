<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 415 — le type de contenu n'est pas pris en charge.
 *
 * Deux causes distinctes, séparées par le code : le `Content-Type` de la REQUÊTE n'est servi par
 * aucun parseur — y compris quand il est absent (`UNSUPPORTED_MEDIA_TYPE`) — ou le type du
 * FICHIER envoyé en multipart est refusé, ou son contenu ne correspond pas à son extension
 * (`UNSUPPORTED_TYPE`). Ce ne sont pas les mêmes corrections : la première tient à l'en-tête, la
 * seconde au fichier.
 */
final class UnsupportedMediaTypeException extends ApiException
{
}
