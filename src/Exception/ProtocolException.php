<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * Le serveur a répondu, mais avec une enveloppe que le contrat ne décrit pas.
 */
final class ProtocolException extends SenndoException
{
    public function __construct(
        public readonly int $status,
        /** Le corps brut, tronqué — utile au support, jamais à une logique. */
        public readonly string $body,
    ) {
        parent::__construct(sprintf(
            'senndo : réponse %d illisible (ni JSON valide, ni enveloppe d\'erreur connue).',
            $status,
        ));
    }
}
