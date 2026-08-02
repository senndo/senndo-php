<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * L'appel a dépassé le délai imparti. Ce que le serveur en a fait reste INCONNU.
 */
final class TimeoutException extends SenndoException
{
    public function __construct(
        /** Le délai dépassé, en secondes. */
        public readonly float $timeout,
        public readonly string $operationId,
    ) {
        parent::__construct(sprintf(
            'senndo : « %s » a dépassé le délai de %s s. L\'état côté serveur est INDÉTERMINÉ : '
            . 'sur un envoi, relisez listMessages avec votre clé d\'idempotence avant de renvoyer '
            . '— un délai dépassé ne prouve pas que rien n\'est parti.',
            $operationId,
            $timeout,
        ));
    }
}
