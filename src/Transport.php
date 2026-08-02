<?php

declare(strict_types=1);

namespace Senndo;

use Senndo\Exception\ConnectionException;
use Senndo\Exception\TimeoutException;

/**
 * Toute implémentation capable d'exécuter une requête HTTP.
 *
 * POURQUOI C'EST UNE INTERFACE ET NON UN DÉTAIL. Le transport par défaut est cURL, sans dépendance.
 * Cela ne doit pas enfermer l'appelant : un projet bâti sur Guzzle, Symfony HttpClient ou un client
 * interne (proxy d'entreprise, mTLS, métriques) passe le sien au constructeur du client et garde la
 * validation, les erreurs typées et la politique de retentative — la partie qu'un `curl_exec`
 * enrobé ne donne pas. PSR-18 aurait imposé `psr/http-client` et `psr/http-message` à tout projet
 * installant le SDK ; un adaptateur de vingt lignes vers PSR-18 s'écrit côté appelant, l'inverse
 * n'est pas vrai.
 *
 * L'implémentation DOIT lever `TimeoutException` sur dépassement de délai et `ConnectionException`
 * sur échec de transport. Elle ne doit JAMAIS lever sur un statut d'erreur HTTP.
 */
interface Transport
{
    /**
     * @throws TimeoutException
     * @throws ConnectionException
     */
    public function send(HttpRequest $request): HttpResponse;
}
