# Google

## Introduction

> Based on the generic example doc.

Google use some genric openId Connect protocol. But :

* scope _offline\_access_ is not authorized
* it's has not a logout endpoint (_end\_session\_endpoint_)

## Get a _refresh\_token_ with google

You can get a _refresh\_token_ with google. Use _set\_access\_type()_ method for that. But :

* The _refresh\_token_ is given the first time you asked for it
* To have a new _refresh\_token_ you have to revoke the first one before

You ask for a _refresh\_token_ in the authorization process :

```php
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://accounts.google.com/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$callback_url = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient\init($disco_url, $client_id);
$client->setSessionKey($session_key);

$auth = $client->authorization($callback_url);

$auth->addScope('email profile');
$auth->set_access_type('offline');; // To get a refresh_token if needed and accepted by your provider
$auth->set_state();
$auth->set_nonce();

$auth->exec();
```
