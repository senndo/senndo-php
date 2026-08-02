<?php

declare(strict_types=1);

/**
 * Exemple exécutable — envoie un SMS, puis relit son verdict.
 *
 *     export SENNDO_API_KEY=sk_test_…
 *     php examples/quickstart.php +33612345678
 *
 * UNE CLÉ `sk_test_` NE DÉPLACE AUCUN ARGENT et ne fait sonner aucun téléphone : c'est celle à
 * utiliser pour vérifier une intégration. Avec une clé `sk_live_`, ce script envoie un vrai message
 * et débite le compte — d'où le refus explicite plus bas si `--live` n'est pas passé.
 *
 * LE VERDICT N'EST PAS DANS LA RÉPONSE DE L'ENVOI. `sendMessage` rend l'état au moment de
 * l'acceptation ; savoir si le message est arrivé demande de le relire, ou d'écouter un webhook.
 */

require __DIR__ . '/../vendor/autoload.php';

use Senndo\Client;
use Senndo\Exception\InsufficientFundsException;
use Senndo\Exception\SenndoException;
use Senndo\Http;

// `$argv` n'existe que si `register_argc_argv` est activé — vrai par défaut en CLI, faux sous
// certaines configurations d'hébergement. On lit donc `$_SERVER`, et on vérifie ce qu'on y trouve.
$brut = $_SERVER['argv'] ?? [];
if (!is_array($brut)) {
    fwrite(STDERR, "Les arguments de la ligne de commande ne sont pas lisibles.\n");
    exit(2);
}
$arguments = array_values(array_filter(array_slice($brut, 1), is_string(...)));
$destinataire = null;
foreach ($arguments as $argument) {
    if (!str_starts_with($argument, '-')) {
        $destinataire = $argument;
        break;
    }
}

if ($destinataire === null) {
    fwrite(STDERR, "usage : php examples/quickstart.php <+E164> [--live]\n");
    exit(2);
}

$cle = getenv('SENNDO_API_KEY');
if (!is_string($cle) || $cle === '') {
    fwrite(STDERR, "SENNDO_API_KEY est absente de l'environnement.\n");
    exit(2);
}

if (str_starts_with($cle, 'sk_live_') && !in_array('--live', $arguments, true)) {
    fwrite(STDERR, "Cette clé est une clé de PRODUCTION : l'envoi sera réel et facturé.\n");
    fwrite(STDERR, "Relancez avec --live si c'est bien ce que vous voulez.\n");
    exit(2);
}

$senndo = new Client(apiKey: $cle);
echo 'client : ' . $senndo . PHP_EOL;

try {
    $solde = $senndo->getBalance();
    echo "solde : {$solde['balanceUsd']} USD" . PHP_EOL;

    $envoi = $senndo->sendMessage([
        'channel' => 'sms',
        'to' => $destinataire,
        'text' => 'senndo — exemple du SDK PHP.',
        // La clé vient d'ici parce qu'un script n'a rien de métier à en dériver. Dans une
        // application, elle vient de VOTRE base : c'est la seule qui survive à un redémarrage entre
        // l'envoi et la réponse.
        'idempotencyKey' => Http::newIdempotencyKey('exemple-php-'),
    ]);
    echo "envoyé : {$envoi['id']} — état initial {$envoi['status']}" . PHP_EOL;

    sleep(3);
    $message = $senndo->getMessage($envoi['id']);
    $code = $message['failureCode'] ?? 'aucun';
    echo "verdict : {$message['status']} (code d'échec : {$code})" . PHP_EOL;
    echo 'facturé : ' . ($message['billedAmountUsd'] ?? '0') . ' USD' . PHP_EOL;
} catch (InsufficientFundsException) {
    fwrite(STDERR, "solde insuffisant — rechargez le compte.\n");
    exit(1);
} catch (SenndoException $erreur) {
    fwrite(STDERR, 'échec senndo : ' . $erreur->getMessage() . "\n");
    exit(1);
}

exit(0);
