<?php

declare(strict_types=1);

namespace Senndo;

/**
 * Ce qui peut être surchargé POUR UN APPEL, sans reconstruire le client.
 */
final class RequestOptions
{
    /**
     * @param float|null                $timeout    Délai maximal en secondes. À défaut, celui du client.
     * @param int|null                  $maxRetries Retentatives. À défaut, celui du client ; 0 les désactive.
     * @param array<string, string>     $headers    En-têtes supplémentaires. `Authorization` n'est PAS surchargeable.
     */
    public function __construct(
        public readonly ?float $timeout = null,
        public readonly ?int $maxRetries = null,
        public readonly array $headers = [],
    ) {
    }
}
