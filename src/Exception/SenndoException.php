<?php

declare(strict_types=1);

namespace Senndo\Exception;

use RuntimeException;

/**
 * La racine de toute erreur levée par ce SDK.
 *
 * `catch (SenndoException $e)` attrape tout ce que le paquet peut lever, et rien d'autre.
 *
 * LA RÈGLE : ON BRANCHE SUR UNE CLASSE OU SUR UN CODE, JAMAIS SUR UN MESSAGE. Le contrat le dit
 * explicitement (`error.code` est stable, `error.message` est un libellé humain qui évolue). Un SDK
 * qui lèverait `new RuntimeException($body)` forcerait chaque intégrateur à lire une chaîne pour
 * savoir s'il doit recharger le compte ou corriger son appel — et à la relire le jour où le libellé
 * change.
 */
abstract class SenndoException extends RuntimeException
{
}
