# Microsoft Azure OIDC

## Introduction

The MS Azure discovery url is https://login.microsoftonline.com/{tenant}/v2.0/.well-known/openid-configuration

{tenant} can take differents value, depending on the configuration you made :
- common
- organizations
- consumers
- your projet tenant id

For the examples, we will use the url *https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration*.

The project organization is set like the that for the example :
- */src* : directory with your scripts
- */data* : datas you will need
- *vendor* : externals library
- *composer.json*
- *composer.lock*

## Authorization

Create a script *authorization.php* in src. This script need to load the vendor *autoload.php* script. 

For authorization, you don't need to authentificate your app

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$callback_url = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient($disco_url, $client_id);
$client->setSessionKey($session_key);

$auth = $client->authorization($callback_url);

$auth->addScope('email profile');
$auth->addScope('offline_access'); // To get a refresh_token if needed
$auth->set_state();
$auth->set_nonce();

$auth->exec();
```

The user is sent to microsoft to authenticate. After authentication, il will be redirect to the url_callback you defined.

## Callback

*callback.php* is the script in */src* directory you have created to deal with the callback after user authentication. It will be used to get user informations.

MS Azure offers 2 possibilities to authenticate your application :
- with a *client_secret* : same functionnalities with others OP
- with a private key : *private_key_jwt* method

>:warning: We will used the second one in the examples.

First of all, you need to have a certificate and its private key. Example to create a self-signed certificate with openssl (you will need to create a secret for the private key) : 
`openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -sha256 -days 365`


Put the *key.pem* and *cert.pem* files in */data* directory.

Register the certificate in you MS Azure console.

For authentication, MS Azure need to receive the *x5t* value of the certificate in the JWS header you will sent. See this example.

The callback script :
```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$cert_path = dirname(__FILE__, 2) . '/data/cert.pem';
$privateKey_path = dirname(__FILE__, 2) . '/data/key.pem';
$privateKey_secret = 'theSecretYouDefined';

$client = new Svgta\OidcClient($disco_url, $client_id);
$client->setSessionKey($session_key);

$tokenRes = $client->token();
$tokenRes->setPrivateKeyFile($privateKey_path, $privateKey_secret);
$cert_Info = Svgta\OidcUtils::getCertInfoFile($cert_path);
$tokenRes->setPrivateKeyX5t($cert_info->x5t);

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

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$cert_path = dirname(__FILE__, 2) . '/data/cert.pem';
$privateKey_path = dirname(__FILE__, 2) . '/data/key.pem';
$privateKey_secret = 'theSecretYouDefined';

$client = new Svgta\OidcClient($disco_url, $client_id);
$client->setSessionKey($session_key);

$tokenRes = $client->token();
$tokenRes->setPrivateKeyFile($privateKey_path, $privateKey_secret);
$cert_Info = Svgta\OidcUtils::getCertInfoFile($cert_path);
$tokenRes->setPrivateKeyX5t($cert_info->x5t);

$tokens = $tokenRes->getTokensFromSession();
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

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$cert_path = dirname(__FILE__, 2) . '/data/cert.pem';
$privateKey_path = dirname(__FILE__, 2) . '/data/key.pem';
$privateKey_secret = 'theSecretYouDefined';

$client = new Svgta\OidcClient($disco_url, $client_id);
$client->setSessionKey($session_key);

$tokenRes = $client->token();
$tokenRes->setPrivateKeyFile($privateKey_path, $privateKey_secret);
$cert_Info = Svgta\OidcUtils::getCertInfoFile($cert_path);
$tokenRes->setPrivateKeyX5t($cert_info->x5t);

$tokens = $tokenRes->getTokensFromSession();

//access_token
$tokenRes->revoke_token($tokens['access_token'], 'access_token');

//refresh_token
$tokenRes->revoke_token($tokens['refresh_token'], 'refresh_token');
```

### Logout

Logout use the session to find the *id_token*. You can give it manually in first argument.

You can specify a logout url for callback. It must been known by th OP.

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';
Svgta\OidcClient::setLogLevel(LOG_DEBUG); //OPTIONNAL, to help to debug

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$logout_url_callback = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient($disco_url);
$client->setSessionKey($session_key);
$client->logout(null, $logout_url_callback);
```