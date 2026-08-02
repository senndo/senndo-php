<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * Une erreur levée AVANT tout appel réseau : le SDK a détecté que la requête serait refusée.
 *
 * Elle n'a pas de statut parce qu'aucune requête n'est partie — et c'est l'intérêt : un champ
 * obligatoire absent ne coûte ni un aller-retour ni, sur un canal facturé, le risque d'un débit.
 */
final class RequestException extends SenndoException
{
}
