# Microsoft Azure OIDC

## Introduction

The MS Azure discovery url is https://login.microsoftonline.com/{tenant}/v2.0/.well-known/openid-configuration

{tenant} can take differents value, depending on the configuration you made :

* common
* organizations
* consumers
* your projet tenant id

For the examples, we will use the url _https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration_.

The project organization is set like that for the example :

* _/src_ : directory with your scripts
* _/data_ : datas you will need
* _vendor_ : externals library
* _composer.json_
* _composer.lock_

**BUG on Azure the 04/03/2023** : the userInfo endpoint return _givenname_ and not _given\_name_ ; same thing for _family\_name_

## Authorization

Create a script _authorization.php_ in src. This script need to load the vendor _autoload.php_ script.

For authorization, you don't need to authentificate your app

```php
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$callback_url = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient\init($disco_url, $client_id);
$client->setSessionKey($session_key);

$auth = $client->authorization($callback_url);
$auth->addScope('email profile');
$auth->addScope('offline_access'); // To get a refresh_token if needed
$auth->set_state();
$auth->set_nonce();
$auth->exec();
```

The user is sent to microsoft to authenticate. After authentication, il will be redirect to the url\_callback you defined.

## Callback

_callback.php_ is the script in _/src_ directory you have created to deal with the callback after user authentication. It will be used to get user informations.

MS Azure offers 2 possibilities to authenticate your application :

* with a _client\_secret_ : same functionnalities with others OP
* with a private key : _private\_key\_jwt_ method

> :warning: We will used the second one in the examples.

First of all, you need to have a certificate and its private key. Example to create a self-signed certificate with openssl (you will need to create a secret for the private key) : `openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -sha256 -days 365`

Put the _key.pem_ (private key) and _cert.pem (certificate)_ files in _/data_ directory.

Register the certificate in you MS Azure console.

For authentication, MS Azure need to receive the _x5t_ value of the certificate in the JWS header you will sent. See this example.

The callback script :

```php
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$cert_path = dirname(__FILE__, 2) . '/data/cert.pem';
$privateKey_path = dirname(__FILE__, 2) . '/data/key.pem';
$privateKey_secret = 'theSecretYouDefined';

$client = new Svgta\OidcClient\init($disco_url, $client_id);
$client->setSessionKey($session_key);
$client->keysManager()
    ->use_for_signVerify()
    ->set_private_key_pem_file($privateKey_path, $privateKey_secret)
    ->set_x509_file($cert_path)
    ->build();

$tokenRes = $client->token();
$tokenRes->jwt_headers_options('x5t');
$tokens = $tokenRes->get_tokens();

$userInfo = $client->userInfo();
```

At this point, you can authorize the user to your application with the _userinfo_ you get.

You can leave the tokens in the session. But, if you get the _refresh\_token_, it's recommended to save it in a database to use it for your personnal reasons.

## Refresh token

You need the _refresh\_token_ get in your callback script.

In the example, the _refresh\_token_ is get from the session.

```php
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$cert_path = dirname(__FILE__, 2) . '/data/cert.pem';
$privateKey_path = dirname(__FILE__, 2) . '/data/key.pem';
$privateKey_secret = 'theSecretYouDefined';

$client = new Svgta\OidcClient\init($disco_url, $client_id);
$client->setSessionKey($session_key);
$client->keysManager()
    ->use_for_signVerify()
    ->set_private_key_pem_file($privateKey_path, $privateKey_secret)
    ->set_x509_file($cert_path)
    ->build();

$tokenRes = $client->token();
$tokenRes->jwt_headers_options('x5t');

$tokens = $tokenRes->getTokensFromSession();
$newTokens = $tokenRes->refresh_token($refresh_token);

```

You must received new _access\_token_ and new _id\_token_. If a new _refresh\_token_ is given, you have to save it.

## Revoke token or logout

In theses examples, the tokens are get from the session.

You can revoke :

* _access\_token_
* _refresh\_token_

It's recommended to do the logout process with the _id\_token_.

### Revoke tokens

```php
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$cert_path = dirname(__FILE__, 2) . '/data/cert.pem';
$privateKey_path = dirname(__FILE__, 2) . '/data/key.pem';
$privateKey_secret = 'theSecretYouDefined';

$client = new Svgta\OidcClient\init($disco_url, $client_id);
$client->setSessionKey($session_key);
$client->keysManager()
    ->use_for_signVerify()
    ->set_private_key_pem_file($privateKey_path, $privateKey_secret)
    ->set_x509_file($cert_path)
    ->build();

$tokenRes = $client->token();
$tokenRes->jwt_headers_options('x5t');

$tokens = $tokenRes->getTokensFromSession();

//access_token
$tokenRes->revoke_token($tokens['access_token'], 'access_token');

//refresh_token
$tokenRes->revoke_token($tokens['refresh_token'], 'refresh_token');
```

### Logout

Logout use the session to find the _id\_token_. You can give it manually in first argument.

You can specify a logout url for callback. It must been known by th OP.

```php
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';
Svgta\OidcClient::setLogLevel(LOG_DEBUG); //OPTIONNAL, to help to debug

$disco_url = 'https://login.microsoftonline.com/consumers/v2.0/.well-known/openid-configuration';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$logout_url_callback = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient\init($disco_url);
$client->setSessionKey($session_key);
$client->logout(null, $logout_url_callback);
```
