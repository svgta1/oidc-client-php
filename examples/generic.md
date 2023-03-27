# Generic use of the library

## Introduction

In theses examples, your provider must accept common openId Connect protocol.

The project organization is set like the that for the example :

```
- /src : directory with your scripts
  | - authorization.php
  | - callback.php
  | - logout.php
- /vendor : externals library
- composer.json
- composer.lock
```

You must known :
- the discovery url of your provider
- the cllient_id
- the client_secret

## Authorization

Create a script *authorization.php* in src. This script need to load the vendor *autoload.php* script.

For authorization, you don't need to authentificate your app
```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://Provider.tld/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$callback_url = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient($disco_url, $client_id);
$client->setSessionKey($session_key);

$auth = $client->authorization($callback_url);

$auth->addScope('email profile');
$auth->addScope('offline_access'); // To get a refresh_token if needed and accepted by your provider
$auth->set_state();
$auth->set_nonce();

$auth->exec();
```

The user is redirect to the provider login page.

## Callback

*callback.php* is the script in */src* directory you have created to deal with the callback after user authentication on the provider. It will be used to get user informations.

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://Provider.tld/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$client_secret = 'yourClient_secret';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';

$client = new Svgta\OidcClient($disco_url, $client_id, $client_secret);
$client->setSessionKey($session_key);

$tokenRes = $client->token();
$tokens = $tokenRes->get_tokens();

$userInfo = $client->userInfo();
```

At this point, you can authorize the user to your application with the *userinfo* you get. 

You can leave the tokens in the session. But, if you get the *refresh_token*, it's recommended to save it a database to use it for your personnal reasons.

## Refresh token

You need the *refresh_token* get in your callback script.

In the example, the *refresh_token* is get from the session.

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://Provider.tld/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$client_secret = 'yourClient_secret';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';

$client = new Svgta\OidcClient($disco_url, $client_id, $client_secret);
$client->setSessionKey($session_key);

$tokenRes = $client->token();
$tokens = $tokenRes->getTokensFromSession();

$refresh_token = $tokens['refresh_token'];
$newTokens = $tokenRes->refresh_token($refresh_token);

```

You must received new *access_token* and new *id_token*. If a new *refresh_token* is given, you have to save it.

## Revoke token or logout

In theses examples, the tokens are get from the session.

You can revoke :
- *access_token*
- *refresh_token*

It's recommended to do the logout process with the *id_token*.

### Revoke tokens

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://Provider.tld/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$client_secret = 'yourClient_secret';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';

$client = new Svgta\OidcClient($disco_url, $client_id, $client_secret);
$client->setSessionKey($session_key);

$tokenRes = $client->token();
$tokens = $tokenRes->getTokensFromSession();

//access_token
$tokenRes->revoke_token($tokens['access_token'], 'access_token');

//refresh_token
$tokenRes->revoke_token($tokens['refresh_token'], 'refresh_token');
```

### Logout

Logout use the session to find the *id_token*. You can give it manually in first argument.

You can specify a logout url for callback. It must been known by the provider.

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://Provider.tld/.well-known/openid-configuration';
$logout_url_callback = 'https://yourAPPurl/callback.php';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';

$client = new Svgta\OidcClient($disco_url);
$client->setSessionKey($session_key);

$client->logout(null, $logout_url_callback);
```
