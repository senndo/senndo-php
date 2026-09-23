# senndo — SDK PHP officiel

Messagerie multicanale et vérification : SMS, WhatsApp, e-mail, voix, OTP. Une seule API, un seul
solde, un verdict de livraison par message.

```bash
composer require senndo/senndo-php
```

PHP ≥ 8.1, extensions `curl` et `json`. **Aucune dépendance Composer.**

---

## Premier envoi

```php
use Senndo\Client;

// La clé se crée dans la console, écran « REST API ». Ne la committez jamais.
$senndo = new Client(apiKey: $cleApi);

$envoi = $senndo->sendMessage([
    'channel' => 'sms',
    'to' => '+33612345678',
    'text' => 'Votre code de connexion est 4821.',
    'idempotencyKey' => "connexion-{$utilisateurId}",
]);

journaliser($envoi['id'], $envoi['status']);
```

`sendMessage` rend l'état **au moment de l'acceptation**, pas le verdict final. Un `sent` dit que
l'opérateur a pris le message ; il ne dit pas qu'il est arrivé. Le verdict se lit sur un webhook, ou
en relisant le message.

```php
$message = $senndo->getMessage($identifiantDuMessage);

if ($message['status'] === 'failed') {
    journaliser('échec', $message['failureCode'] ?? null);
}
```

---

## Tous les canaux

Le même appel sert les six canaux ; seul le contenu change.

```php
// E-mail : `senderId` est une adresse vérifiée de votre compte, `subject` est obligatoire.
$senndo->sendMessage([
    'channel' => 'email',
    'to' => 'client@example.com',
    'senderId' => 'contact@example.com',
    'subject' => 'Votre commande est expédiée',
    'text' => 'Bonjour, votre colis est en route.',
    'idempotencyKey' => "expedition-{$utilisateurId}",
]);

// WhatsApp Twilio : un modèle Twilio approuvé (`HX…`) et ses variables numérotées.
$senndo->sendMessage([
    'channel' => 'whatsapp_twilio',
    'to' => '+33612345678',
    'content' => ['sid' => 'HX00000000000000000000000000000000', 'variables' => ['1' => '4821']],
    'idempotencyKey' => "otp-twilio-{$utilisateurId}",
]);
```

Un nom de modèle WhatsApp Cloud qui existe en plusieurs langues exige `template.language`
(`'template' => ['name' => '…', 'language' => 'fr']`), sans quoi l'envoi est refusé en
`422 TEMPLATE_LANGUAGE_REQUIRED`. Ce que votre compte peut réellement utiliser se lit avant
d'envoyer :

```php
$actifs = [];
foreach ($senndo->listSenderIds()['senderIds'] as $expediteur) {
    if ($expediteur['lifecycleStatus'] === 'active') {
        $actifs[] = $expediteur['value'];
    }
}
$modeles = $senndo->listWaTemplates()['templates'];
journaliser($actifs, array_column($modeles, 'language', 'name'));
```

## Quand un statut est-il définitif ?

`delivered`, `read` et `failed` sont définitifs. `sent` dit que l'opérateur a pris le message en
charge ; tant que `verdictPending` vaut `true`, aucune preuve de remise n'est encore arrivée.
Certaines routes n'émettent jamais d'accusé de remise : `sent` peut alors rester le dernier mot.
Un `failed` rendu par le fournisseur avant toute remise est contre-passé :
`reversedAmountUsd` porte le montant rendu.

---

## La clé d'idempotence : le SDK n'en fabrique pas à votre place

`idempotencyKey` est **obligatoire** sur tout envoi, et c'est délibéré. Rejouer la même clé renvoie
le message déjà créé — sans jamais redébiter le compte.

Le SDK **n'en pose jamais une pour vous**. Une clé inventée au moment de l'appel serait perdue si le
processus meurt entre l'envoi et la réponse : exactement le cas où l'idempotence sert. La bonne clé
vient de votre domaine.

```php
// CORRECT : la clé survit à un redémarrage, parce qu'elle vient de votre base.
$senndo->sendMessage([
    'channel' => 'whatsapp_cloud',
    'to' => $commande['telephone'],
    'text' => 'Votre commande est prête.',
    'idempotencyKey' => "commande-{$commande['reference']}-prete",
]);
```

`Http::newIdempotencyKey()` existe pour les cas où il n'y a **vraiment** rien à dériver — un envoi
manuel depuis un script, un test :

```php
$senndo->sendMessage([
    'channel' => 'sms',
    'to' => '+15551234567',
    'text' => 'Essai.',
    'idempotencyKey' => Http::newIdempotencyKey('essai-'),
]);
```

---

## Les erreurs se branchent sur une classe, jamais sur un message

```php
try {
    $senndo->sendMessage([
        'channel' => 'sms',
        'to' => '+22507000000',
        'text' => 'Bonjour.',
        'idempotencyKey' => "bienvenue-{$utilisateurId}",
    ]);
} catch (InsufficientFundsException) {
    rechargerLeCompte();
} catch (ValidationException $erreur) {
    journaliser('appel à corriger', $erreur->errorCode, $erreur->apiMessage);
} catch (RateLimitException $erreur) {
    attendre($erreur->retryAfter ?? 5);
} catch (SenndoException $erreur) {
    journaliser('échec senndo', $erreur->getMessage());
}
```

`$erreur->errorCode` est **stable** ; `$erreur->apiMessage` est un libellé humain qui évolue. Ne
branchez jamais sur le second.

> **Pourquoi `errorCode` et non `code`.** `Exception::$code` existe déjà en PHP, il est de type
> `int` et il n'est pas en lecture seule : le redéclarer en `string` est refusé par le moteur. C'est
> la seule asymétrie de nommage entre les SDK TypeScript, Python et PHP, et elle est imposée par le
> langage.

Le code d'échec d'un message est en union **ouverte** : senndo ajoute des valeurs, n'en retire pas.

```php
$message = $senndo->getMessage($identifiantDuMessage);
$code = $message['failureCode'] ?? null;

if ($code !== null && !in_array($code, Contract::KNOWN_FAILURE_CODES, true)) {
    journaliser('code plus récent que ce SDK', $code);
}
```

---

## Les montants sont des chaînes décimales

Un `NUMERIC(18,6)` passé par un flottant perd des unités sur les longues traînes, et un prix
unitaire sub-centime arrondi à deux décimales devient zéro. Le SDK ne convertit rien : les montants
arrivent en `string` et se somment avec `bcadd`.

```php
$solde = $senndo->getBalance(['currency' => 'EUR']);
journaliser('disponible', $solde['balanceUsd']);

$journal = $senndo->listLedger(['pageSize' => 100]);
$mouvementNet = '0';
foreach ($journal['rows'] as $ligne) {
    // Le SDK ne CONVERTIT pas les montants, il ne peut donc pas certifier qu'ils sont numériques :
    // la vérification est explicite, et un montant illisible fait échouer le calcul plutôt que de
    // le fausser en silence.
    if (!is_numeric($ligne['amountUsd'])) {
        throw new \RuntimeException("montant illisible : {$ligne['amountUsd']}");
    }
    $mouvementNet = bcadd($mouvementNet, $ligne['amountUsd'], 6);
}
journaliser('mouvement net', $mouvementNet);
```

---

## Retentatives : ce qui est rejoué, et ce qui ne l'est jamais

Le SDK retente **uniquement** ce qui peut l'être sans conséquence :

| Appel | Retenté ? |
|---|---|
| `GET`, `DELETE` | oui — sur échec de transport, 429, 5xx |
| `sendMessage` (porte une clé d'idempotence) | oui |
| `createWebhook`, `estimateMessage`, `revokeWebhook` | **jamais** |
| tout `4xx` autre que 429 | jamais |

`createWebhook` crée une ressource à chaque exécution : une retentative aveugle produirait deux
endpoints, donc deux livraisons pour chaque événement.

```php
$senndo->sendMessage(
    [
        'channel' => 'sms',
        'to' => '+5511998877665',
        'text' => 'Ping.',
        'idempotencyKey' => "ping-{$tentative}",
    ],
    new RequestOptions(timeout: 10.0, maxRetries: 0),
);
```

---

## Téléverser un média

```php
$fichier = $senndo->uploadMedia(
    new MultipartUpload($octets, 'facture.pdf', 'application/pdf'),
);

$senndo->sendMessage([
    'channel' => 'whatsapp_cloud',
    'to' => '+33612345678',
    'text' => 'Votre facture.',
    'media' => ['ref' => $fichier['ref']],
    'idempotencyKey' => "facture-{$commande['reference']}",
]);
```

---

## Brancher votre propre client HTTP

Le transport par défaut est cURL — zéro dépendance. Un projet qui a déjà Guzzle, Symfony HttpClient,
un proxy d'entreprise ou du mTLS injecte le sien, et garde la validation, les erreurs typées et la
politique de retentative.

```php
final class TransportMaison implements Transport
{
    public function send(HttpRequest $request): HttpResponse
    {
        $reponse = appelerMonClient(
            $request->method,
            $request->url,
            $request->headers,
            $request->body,
            $request->timeout,
        );

        return new HttpResponse($reponse['status'], $reponse['headers'], $reponse['body']);
    }
}
```

Le transport doit lever `TimeoutException` sur dépassement de délai et `ConnectionException` sur
échec de transport, et **ne jamais lever** sur un statut d'erreur HTTP — sinon le code stable de
l'enveloppe est perdu.

---

## La clé API ne s'imprime pas

```php
journaliser((string) $senndo);  // Senndo\Client { baseUrl: …, apiKey: sk_live_…32 caractères masqués }
```

Il n'existe aucun accesseur qui rende la clé en clair, et `__debugInfo` masque également : un
`var_dump` ou un dumper de framework ne peut pas la révéler.

---

## Webhooks

```php
$endpoint = $senndo->createWebhook([
    'name' => 'Production',
    'url' => 'https://exemple.test/senndo',
    'events' => ['message.sent', 'message.failed'],
]);

conserverLeSecret($endpoint['secret']);
```

Le secret n'est lisible **qu'à la création**. Il signe chaque livraison : vérifiez la signature avant
de faire quoi que ce soit du corps.

---

## Licence

MIT. Voir [CHANGELOG.md](CHANGELOG.md) pour les changements de version.
