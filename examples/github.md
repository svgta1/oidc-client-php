# Github

## Introduction
> Based on the generic example doc.

Github has not a discovery url. It's using OAUTH2 and not OpenId connect.

You have to set manually the configurations to access to the user informations.

## Authorization

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$callback_url = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient();
$client->setSessionKey($session_key);
$client->client_id($client_id);
$client->add_OP_info('authorization_endpoint', 'https://github.com/login/oauth/authorize');


$auth = $client->authorization($callback_url);
$auth->set_state();

$auth->exec();
```

## Callback

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$client_id = 'yourClient_id';
$client_secret = 'yourClient_secret';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';

$client = new Svgta\OidcClient();
$client->setSessionKey($session_key);
$client->client_id($client_id);
$client->client_secret($client_secret);
$client->add_OP_info('token_endpoint', 'https://github.com/login/oauth/access_token');
$client->add_OP_info('userinfo_endpoint', 'https://api.github.com/user');

$tokenRes = $client->token();
$tokens = $tokenRes->get_tokens();

$userInfo = $client->userInfo();
```
