# Google 

## Introduction

> Based on the generic example doc.


Google use some genric openId Connect protocol. But :
- scope *offline_access* is not authorized
- it's has not a logout endpoint (*end_session_endpoint*)

## Get a *refresh_token* with google

You can get a *refresh_token* with google. Use *set_access_type()* method for that. But :
- The *refresh_token* is given the first time you asked for it
- To have a new *refresh_token* you have to revoke the first one before

You ask for a *refresh_token* in the authorization process :

```PHP
<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$disco_url = 'https://accounts.google.com/.well-known/openid-configuration';
$client_id = 'yourClient_id';
$session_key = 'YWQ4Q1Hpb_zQliS5wGYDDPZm2xC7PzyfjgLKBNodkazkN_pEPlm7yVBw5r9_pDzSwHJRsFVZShQyb_LFUSMBGQ';
$callback_url = 'https://yourAPPurl/callback.php';

$client = new Svgta\OidcClient($disco_url, $client_id);
$client->setSessionKey($session_key);

$auth = $client->authorization($callback_url);

$auth->addScope('email profile');
$auth->set_access_type('offline');; // To get a refresh_token if needed and accepted by your provider
$auth->set_state();
$auth->set_nonce();

$auth->exec();
```