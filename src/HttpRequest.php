<?php

declare(strict_types=1);

namespace Senndo;

/**
 * Une requête HTTP, réduite à ce que le SDK produit.
 */
final class HttpRequest
{
    /**
     * @param array<string, string> $headers
     * @param float                 $timeout Délai maximal, en secondes, pour CETTE tentative.
     */
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public readonly array $headers,
        public readonly ?string $body,
        public readonly float $timeout,
    ) {
    }
}
