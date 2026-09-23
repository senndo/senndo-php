<?php

declare(strict_types=1);

namespace Senndo\Exception;

/**
 * 400 / 422 — la requête est mal formée, ou irrecevable en l'état. Corrigez l'appel.
 *
 * Le corps peut n'avoir jamais été LU : `EMPTY_BODY` (corps annoncé en JSON mais vide) et
 * `MALFORMED_JSON` (corps illisible) sont rendus par le serveur avant d'atteindre la route, donc
 * avant toute validation métier. Les autres codes de ce statut concernent un corps bien formé
 * mais refusé sur le fond. Dans tous les cas la reprise est inutile tant que l'appel n'a pas
 * changé — c'est ce qui sépare ce statut d'un 5xx.
 */
final class ValidationException extends ApiException
{
}
