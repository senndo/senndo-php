<?php

declare(strict_types=1);

namespace Senndo;

/**
 * Vérification de la signature d'un webhook senndo.
 *
 * Chaque livraison porte `X-Senndo-Signature: t=<unix>,v1=<hex>`, où `v1` est le HMAC-SHA256 de
 * `"{t}.{corps}"` sous le secret `whsec_…` rendu à la création de l'endpoint. La signature est
 * recalculée à chaque tentative : une tolérance de quelques minutes sur `t` ne rejette jamais une
 * retentative légitime, et refuse un rejeu ancien.
 */
final class Webhook
{
    /**
     * `true` si l'en-tête authentifie `$rawBody` sous `$secret`, dans la tolérance. `$rawBody`
     * doit être le corps BRUT reçu (`file_get_contents('php://input')`) : un JSON re-sérialisé ne
     * vérifie pas.
     */
    public static function verifySignature(
        string $secret,
        ?string $header,
        string $rawBody,
        int $toleranceSeconds = 300,
        ?int $now = null,
    ): bool {
        if ($header === null || $secret === '') {
            return false;
        }
        if (preg_match('/^t=(\d+),v1=([0-9a-f]{64})$/', trim($header), $match) !== 1) {
            return false;
        }
        $timestamp = (int) $match[1];
        if (abs(($now ?? time()) - $timestamp) > $toleranceSeconds) {
            return false;
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);

        return hash_equals($expected, $match[2]);
    }
}
