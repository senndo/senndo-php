<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * Toute réponse d'erreur RENVOYÉE PAR L'API — l'enveloppe `{"error": {"code", "message"}}`.
 *
 * LA FAMILLE VIENT DU STATUT HTTP, PAS DU CODE. Les statuts sont peu nombreux et stables ; les
 * codes, eux, s'ajoutent (senndo s'y engage). Une classe par code aurait rendu toute nouvelle
 * valeur amont invisible du `catch` d'un client déjà déployé.
 */
class ApiException extends SenndoException
{
    /** L'attente demandée par un 429, quand le serveur la précise. */
    public ?int $retryAfter = null;

    public function __construct(
        /** Le statut HTTP. */
        public readonly int $status,
        /**
         * Le code STABLE. Branchez votre logique dessus.
         *
         * IL S'APPELLE `errorCode` ET NON `code`, contrairement au SDK TypeScript et au SDK Python.
         * `Exception::$code` existe déjà en PHP, il est de type `int` et il n'est pas en lecture
         * seule : le redéclarer en `string` est refusé par le moteur comme par l'analyse statique.
         * La symétrie de nommage entre les trois SDK s'arrête donc ici, et c'est le langage qui
         * l'impose, pas un choix.
         */
        public readonly string $errorCode,
        /** Le libellé humain renvoyé par l'API. Ne branchez RIEN dessus. */
        public readonly string $apiMessage,
        /** L'opération concernée. */
        public readonly string $operationId,
    ) {
        parent::__construct(sprintf(
            'senndo : %s → %d %s — %s',
            $operationId,
            $status,
            $errorCode,
            $apiMessage,
        ));
    }
}
