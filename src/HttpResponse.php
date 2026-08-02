<?php

declare(strict_types=1);

namespace Senndo;

/**
 * Une réponse HTTP, réduite à ce que le SDK consomme.
 *
 * `status` porte le code même quand il vaut 4xx ou 5xx : un transport qui lèverait sur un 402
 * priverait le SDK de l'enveloppe d'erreur, donc du code stable sur lequel l'appelant branche.
 */
final class HttpResponse
{
    /**
     * @param array<string, string> $headers Noms en minuscules.
     */
    public function __construct(
        public readonly int $status,
        public readonly array $headers,
        public readonly string $body,
    ) {
    }
}
