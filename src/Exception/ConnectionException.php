<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * Le transport a échoué : DNS, TLS, coupure. Aucune réponse HTTP n'a été reçue.
 */
final class ConnectionException extends SenndoException
{
    public function __construct(
        public readonly string $operationId,
        /** Le détail remonté par le transport — utile au support, jamais à une logique. */
        public readonly string $detail = '',
    ) {
        parent::__construct(sprintf(
            'senndo : « %s » n\'a pas abouti (échec de transport).%s',
            $operationId,
            $detail === '' ? '' : ' ' . $detail,
        ));
    }
}
