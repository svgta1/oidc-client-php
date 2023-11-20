# Token endpoint

## Introduction

The library try to choose the best authentication method depending of the OP options for the endpoint token. But you can force it. The methods allowed are :

* **pkce** : if you used pkce for authorization (no client\_secret needed)
* **client\_secret\_basic** (client\_secret needed)
* **client\_secret\_post** (client\_secret needed)
* **client\_secret\_jwt** (client\_secret needed)
* **private\_key\_jwt** (no client\_secret needed)

For that, you need to use the method `set_auth_method`.

Example : force to client\_secret\_basic

```php
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);

$tokenRes = $client->token();
$tokenRes->set_auth_method('client_secret_basic'); // optional, to force the authentication method

...
```

## Usign the method _client\_secret\_jwt_

The authentication to the _token\_endpoint_ is made by sending a JWT signed with the client\_ secret. The default algorithm used by the libray is HS256

> The [RFC 7518](https://www.rfc-editor.org/rfc/rfc7518#section-3.2)  indicate the minimum length that client\_secret must have

The JWT can be signed with :

* HS256 : minimum length of client\_secret 256 bits
* HS384 : minimum length of client\_secret 384 bits
* HS512 : minimum length of client\_secret 512 bits

```PHP
// Example
  $tokenRes->setSigAlg('HS512');
```

## Usign the method _private\_key\_jwt_

The authentication to the _token\_endpoint_ is made by sending a JWT signed with a RSA or Elliptic private key. The public key or certificate must be known by the OP.

The private key can be given in multiple format :

```php

//From PEM
  $privateKey = <<<EOD
  -----BEGIN EC PRIVATE KEY-----
  MHcCAQEEINrfGx+a3flbw/2bjiiDkF8+VMpqjE751+ILDkzxM8FvoAoGCCqGSM49
  AwEHoUQDQgAED2XFGdEmpygLSqqn5SMXeR740smRBfULJet3hzkUZ+YySKzjCHkS
  LVxw3dimCk14de2ANcVxosOU5hOCP6SDBw==
  -----END EC PRIVATE KEY-----
  EOD;

  $client->keysManager()
    ->set_private_key_pem($privateKey, $password) // $password is OPTIONNAL. Set it if the key is protected by a password
    ->use_for_signVerify()
    ->set_kid('my key id') //optionnal
    ->build();

//From file contening pem
  $client->keysManager()
    ->set_private_key_pem_file($pathOfFile, $password) // $password is OPTIONNAL. Set it if the key is protected by a password
    ->use_for_signVerify()
    ->set_kid('my key id') //optionnal
    ->build();

//From p12 file
  $client->keysManager()
    ->set_p12_file($pathOfFile, $password) // $password is OPTIONNAL. Set it if the p12 is protected by a password
    ->use_for_signVerify()
    ->set_kid('my key id') //optionnal
    ->build();

//From X509 certificate
  $cert = <<<EOD
  -----BEGIN CERTIFICATE-----
  // Certificate informations to PEM format
  -----END CERTIFICATE-----
  EOD;

  $client->keysManager()
    ->set_private_key_pem($privateKey, $password) // $password is OPTIONNAL. Set it if the key is protected by a password
    ->set_x509($cert)
    ->use_for_signVerify()
    ->set_kid('my key id') //optionnal
    ->build();

//From X509 certificate file
  $client->keysManager()
    ->set_private_key_pem_file($pathToPrivateKey, $password) // $password is OPTIONNAL. Set it if the key is protected by a password
    ->set_x509($pathToCert)
    ->use_for_signVerify()
    ->set_kid('my key id') //optionnal
    ->build();

//Use certificate Info for the signed JWT header in the tokens requests
// -- used to authentificate to microsoft azure with a certificate
  $tokenRes = $client->token();
  $tokenRes->jwt_headers_options('kid'); //add the kid from the certificate
  $tokenRes->jwt_headers_options('x5t'); //add x5t from the certificate
  
```

For RSA key, the default algorithm used by the library is RS256. Theses algorithms can be used :

* RS256
* RS384
* RS512
* PS256
* PS384
* PS512

```php
// Example
  $tokenRes->setSigAlg('PS512');
```

For Ellyptic key, the algorithm is automatically set from the curve present in the key :

* P-256 : ES256
* P-384 : ES384
* P-521 : ES512
