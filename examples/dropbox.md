# Dropbox

## Introduction
> Based on the generic example doc.

Dropbox use limited functionnality of openId connect :
- No userinfo endpoint usable in OIDC process
- No revokation endpoint
- No logout endpoint
- *Nonce* is not supported
- Refresh token not supported
- ...


## Authorization

You must not set *$auth->set_nonce();*
```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://www.dropbox.com/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$callback_url = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient($disco_url, $client_id);
$client->setSessionKey($session_key);

$auth = $client->authorization($callback_url);

$auth->addScope('email profile');
$auth->set_state();
$auth->set_nonce();

$auth->exec();
```

## Callback

You can not get userInfo from an userInfo enpoint. You can get some informations from the *id_token* directly.

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://www.dropbox.com/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$client_secret = 'yourClient_secret';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';

$client = new Svgta\OidcClient($disco_url, $client_id, $client_secret);
$client->setSessionKey($session_key);

$tokenRes = $client->token();
$tokens = $tokenRes->get_tokens();

$userInfo = $tokensRes->get_id_token_payload();
```

