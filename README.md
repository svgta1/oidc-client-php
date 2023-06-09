[TOC]

# OpenId Connect Client for PHP
A library that allows appllications to authentificate a user through the OpenId Connect flow.

## Requirements
* PHP 8.1 or greater
* curl extension
* mb extension
* json extension
* openssl

## Supported

**Authentication**
- [x] pkce
- [x] client_secret_basic 
- [x] client_secret_post 
- [x] client_secret_jwt 
- [x] private_key_jwt 
- [x] client credential
- [x] password grant
- [ ] JWE Encryption
- [ ] Nested JWT (JWS encrypted in a JWE)
---
**Claims request**
- [x] Scope
- [ ] Request parameter
- [ ] JWS, JWE, Nested JWT request parameter
---
**id_token**
- [x] Signed JWT (JWS)
- [x] Nested JWT (JWS encrypted in a JWE)
---
**UserInfo**
- [X] Json
- [x] Signed JWT (JWS)
- [X] Encrypted Json
- [x] Nested JWT (JWS encrypted in a JWE)
---
**Tokens**
- [x] Refresh
- [x] Revoke
- [x] Introspect
---
**Logout**
- [x] Front-channel logout
- [ ] Back-channel logout
---
**Dynamic Registration**
- [x] Registration
- [x] Update
- [x] Delete
---


## How to install

Composer is the best way to install the library with all its dependencies.

```shell
composer require svgta/oidc-client-php
```

In your PHP script, include composer autoload. Example : 
```PHP
require dirname(__FILE__, 2) . '/vendor/autoload.php';
```
## How to use

### Initialize

#### Basic usage
```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret', //OPTIONNAL, depend on flow used
);
$client->setSessionKey('aSecureKey'); //RECOMMENDED, encrypt datas in the session

// OR
$client = new Svgta\OidcClient\init('https://id.provider.com/.well-known/openid-configuration');
$client->setSessionKey('aSecureKey'); //RECOMMENDED, encrypt datas in the session
$client->client_id('Your_client_id');
$client->client_secret('Your_client_secret');
```

if the /.well-known/openid-configuration does not exist, you can add the OP informations manually :
```PHP
  $client = new Svgta\OidcClient\init();
  $client->setSessionKey('aSecureKey'); //RECOMMENDED, encrypt datas in the session

  $client->add_OP_info('authorization_endpoint', 'https://id.provider.com/auth');
  $client->add_OP_info('token_endpoint', 'https://id.provider.com/token');
  $client->add_OP_info('userinfo_endpoint', 'https://id.provider.com/user');
  $client->add_OP_info('issuer', 'https://id.provider.com');
  // ...
  $client->client_id('Your_client_id');
  $client->client_secret('Your_client_secret')
```

The library use *guzzlehttp/guzzle* to access to the provider endpoints.

---

#### Options

Secure session. The data are encrypted in JWE format. This option is RECOMMENDED.

```PHP
$client->setSessionKey('aSecureKey'); //OPTIONNAL, encrypt datas in the session
```

Adding a proxy : (https://docs.guzzlephp.org/en/stable/request-options.html#proxy)
```PHP
$client->request->setHttpProxy('http://proxyUri:proxyPort');
$client->request->setHttpsProxy('http://proxyUri:proxyPort');
//the proxy is a string
$client->request->setNoProxy(['.myDomain', '.myOtherDomain']);
// the list is an array of domains that don't use proxy if http proxy and https proxy are set
```

No verify TLS : (https://docs.guzzlephp.org/en/stable/request-options.html#verify)
```PHP
$client->request->verifyTls(false);
```

Add SSL key : (https://docs.guzzlephp.org/en/stable/request-options.html#ssl-key)
```PHP
$client->request->setCert('pathToFile');
//OR
$client->request->setCert(['pathToFile', 'password']);
```

Add other Guzzle Request Options : (https://docs.guzzlephp.org/en/stable/request-options.html) -> use the method `addOtherParam(string $key, mixed $value)`. Example :
```PHP
$client->request->addOtherParam('debug', true);

```
---

#### Log level
The default log level is `LOG_ERR`. To change it : 
```PHP
Svgta\OidcClient\init::setLogLevel(LOG_DEBUG);
```
The parameter is an PHP constant in this list : 
- LOG_EMERG
- LOG_ALERT
- LOG_CRIT
- LOG_ERR
- LOG_WARNING
- LOG_NOTICE
- LOG_INFO
- LOG_DEBUG

---
### Examples

You can get some examples on how to use the library in the directory `/examples` :
- [Generic usage](./examples/generic.md)
- [Google specificities](./examples/google.md)
- [Dropbox specificities](./examples/dropbox.md)
- [Github specificities](./examples/Github.md)
- [Microsoft Azure *`private_key_jwt`* authentication](./examples/msAzure.md)

### Authorization
This section use the specification discribed to the url https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowSteps

Basic usage default :
* scope used : openid
* response_type : code

Example of general usage
```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id'
);
//...
//add $client->request options if needed
//..

$auth = $client->authorization('https://your_callback_uri');
$auth->addScope('email profile');
$auth->set_state(); //RECOMMENDED
$auth->set_nonce(); //RECOMMENDED

$auth->exec(); // header location to the OP url
```

Set *state* - RECOMMENDED :
```PHP
$auth->set_state();
```

Set *nonce* - OPTIONNAL but usefull for security reasons.
```PHP
$auth->set_nonce();
```

Add *scopes* :
```PHP
$auth->addScope('email profile offline_access');
// OR in 3 steps
$auth->addScope('email');
$auth->addScope('profile');
$auth->addScope('offline_access');
```

Change *response_type* (set to *code* by default):
```PHP
$auth->set_response_type('code id_token');
// value MUST be one of the follow :
// - code (code flow)
// - id_token (implicit flow)
// - token id_token (implicit flow)
// - code token (hybrid flow)
// - code id_token (hybrid flow)
// - code token id_token (hybrid flow)
```

Use *PKCE* :
```PHP
$auth->set_code_challenge_method('S256');
//Value are S256 or plain

```

Other Options (details on https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest):
```PHP
$auth->set_prompt('none');
// If set, value MUST be none, login, consent OR select_account

$auth->set_response_mode('form_post');
//Change response_mode

$auth->set_display('page');
// If set, value MUST be page, popup, touch OR wap

$auth->set_acr_values($value); //value is a STRING
$auth->set_login_hint($value); //value is a STRING
$auth->set_id_token_hint($value); //value is a STRING
$auth->set_ui_locales($value); //value is a STRING
$auth->set_max_age($value); //value is an INTERGER
//
$auth->set_access_type('offline'); //To get the refresh_token on google

```
---

### Token endpoint authentication

#### Basic usage
The library use JWT Framework (https://web-token.spomky-labs.com/) to deal with authentication to the token_endpoint and the id_token verifications.

The library try to choose the best authentication method depending of the OP options for the endpoint token. But you can force it. The methods allowed are : 
- **pkce** : if you used pkce for authorization (no client_secret needed)
- **client_secret_basic** (client_secret needed)
- **client_secret_post** (client_secret needed)
- **client_secret_jwt** (client_secret needed)
- **private_key_jwt** (no client_secret needed)

```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);

$tokenRes = $client->token();
$tokenRes->set_auth_method('client_secret_basic'); // optional, to force the authentication method

...
```
---
#### Usign the method *client_secret_jwt* 

The authentication to the *token_endpoint* is made by sending a JWT signed with the client_ secret.
The default algorithm used by the libray is HS256
> The RFC 7518 (https://www.rfc-editor.org/rfc/rfc7518#section-3.2) indicate the minimum length that client_secret must have

The JWT can be signed with :
* HS256 : minimum length of client_secret 256 bits
* HS384 : minimum length of client_secret 384 bits
* HS512 : minimum length of client_secret 512 bits
```PHP
// Example
  $tokenRes->setSigAlg('HS512');
```
---
#### Usign the method *private_key_jwt* 

The authentication to the *token_endpoint* is made by sending a JWT signed with a RSA or Elliptic private key. The public key or certificate must be known by the OP.

The private key can be given in multiple format : 
```PHP

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
- RS256
- RS384
- RS512
- PS256
- PS384
- PS512
```PHP
// Example
  $tokenRes->setSigAlg('PS512');
```

For Ellyptic key, the algorithm is automatically set from the curve present in the key :
- P-256 : ES256
- P-384 : ES384
- P-521 : ES512

---

### Tokens management

#### Information
All tokens get by the differents methods are set in session.

The *id_token* must be a JWS (JWT signed by the OP with a key known in it's *jwks_uri* endpoint or signed with the *client_secret*).

---

#### Flow code, implicit, hybrid
Generaly used by the callback url after the authorization on the OP for code or hybrid.

Basic usage :

```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);

$tokenRes = $client->token();
// add options for authentication if needed
// example : $tokenRes->set_auth_method('client_secret_post');
//
$tokens = $tokenRes->get_tokens(); 
```

---

#### Password grant

The *Password grant* flow should not be used. See explanation on :  https://www.oauth.com/oauth2-servers/access-tokens/password-grant/

If your OP don't accept it, you can not used it.

Basic usage :

```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);

$tokenRes = $client->token();
// add options for authentication if needed
// example : $tokenRes->set_auth_method('client_secret_post');
//
$tokens = $tokenRes->password_grant($username, $password); 
```
---

#### Client credentials

This flow is used when applications request an *access_token* to access their own resources.

Basic usage :

```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);

$tokenRes = $client->token();
// add options for authentication if needed
// example : $tokenRes->set_auth_method('client_secret_post');
//
$scopes = 'write read';
$tokens = $tokenRes->client_credentials($scopes); //$scopes is optionnal
```

---

#### Refresh token

To get new *access_token* and *id_token*. The refresh_token must be send with the others tokens. Generaly, in the authorization flow, the scope *offline_access* must be used.

Basic usage :

```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);

$tokenRes = $client->token();
// add options for authentication if needed
// example : $tokenRes->set_auth_method('client_secret_post');
//
$tokens = $tokenRes->refresh_token($refresh_token); 
// the var refresh_token is optionnal. If not set, the library try to find it in its session.
```

---

#### Introspect token

The OP must have *introspection_endpoint* set.
> The instrospection endpoint is not defined in OpenId Connect Provider Metadata (https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata). You can add it with (it's an example) : 
> ```PHP
>  $client->add_OP_info('introspection_endpoint', 'https://id.provider.com/intro');
> `

Based on rfc7662 (https://www.rfc-editor.org/rfc/rfc7662), the token must be an *access_token* or a *refresh_token*.


**Usage :**
```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);
// ...

$tokenRes = $client->token();
// ...

$token = '...AccessTokenValue';
$type = 'access_token';
// OR
$token = '...refreshTokenValue';
$type = 'refresh_token';
// $type is optional. If set, it must have 'refresh_token' or 'access_token' value
//..
$revokeResponse = $tokens->introspect_token($token, $type); 
```

---

#### Revoke token

Only *access_token* and *refresh_token* can be used. 

The OP must have *revocation_endpoint* set.
> The revocation endpoint is not defined in OpenId Connect Provider Metadata (https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata). You can add it with (it's an example) : 
> ```PHP
>  $client->add_OP_info('revocation_endpoint', 'https://id.provider.com/revoke');
> ```

**Usage :**
```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);
// ...

$tokenRes = $client->token();
// ...

$token = '...AccessTokenValue';
$type = 'access_token';
// OR
$token = '...refreshTokenValue';
$type = 'refresh_token';
// $type is optionnal. If set, it must have 'refresh_token' or 'access_token' value
//..
$revokeResponse = $tokens->revoke_token($token, $type); 
```

---

### Get userInfo

The *userinfo_endpoint* need the *access_token*. If it's in session, you don't need to give it back. 

The *id_token* is required to verify the *sub* claim. If it's in session, you don't need to give it back.

The library support the response in *json* format and *jwt* (jwt signed by the OP with a key known in it's *jwks_uri* endpoint or signed with the *client_secret*).


```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);

// ...

$tokenRes = $client->token();
// ...
$tokens = $tokenRes->get_tokens();
// ...
$userInfo = $client->userInfo(); 
// method : $client->userInfo($access_token = null, $id_token = null);
// access_token and id_token are optionals if set in session (the method $tokenRes->get_tokens() do it)

```

> :warning: *userinfo_endpoint* not set
>
> Some OP don't give an *userinfo_endpoint* (Dropbox is an example). If the contents of the *id_token* is enough for you, you can get the result of the *payload*
> ```PHP
> ...
> $tokenRes = $client->token();
> $tokens = $tokenRes->get_tokens();
> $payload = $tokenRes->get_id_token_payload(); //result is an array
>```
> If you call *$client->userInfo()* but the OP don't have the *userinfo_endpoint* set, you will get an *Svgta\OidcException*
---



### Logout
Based on OpenID Connect Session Management 1.0 - draft 17 (https://openid.net/specs/openid-connect-session-1_0-17.html)

The OP must have the endpoint *end_session_endpoint*. It's recommended to give the *id_token* in the request. If not set, the library tries to find it in its session.

The *redirect_uri* can be used by the OP to redirect the user to the page defined. The *redirect_uri* has to be registred in the OP configuration.

Basic :
```PHP
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration'
);

$id_token = 'The_Id_Token_You_Get'; //format jwt string
$redirect_uri = 'https://yourApp.tld/logoutCallback';

$client->logout($id_token, $redirect_uri);
// $id_token and $redirecti_uri are optionals. If id_token is not given, the library try to find it in the session.
// if you don't give id_token but give redirect_uri : $client->logout(null, $redirect_uri);
```

## Advanced use
### Nested JWT

A Nested JWT is a JWT signed before encryption (a JWS in a JWE).

The library can automatically deal with Nested JWT for *userinfo response* and *id_token*. But, the library must known the *private key* or the *secret* to be used to decrypt the token received. It's has been defined with the OP.

To verify the JWS, the library use the *client_secret* or the OP *jwks_uri* like the process of a JWS *id_token*.

In the examples, you have instantiate $client like seen before. You must set the parameters below before calling *tokens* methods or *userInfo* method

Cases :
 - **The key to be used is the client_secret** :
 You have nothing to do

 - **The key is a shared key (secret)** :
```PHP

$client->keysManager()
  ->use_for_encDec(),
  ->set_kid('The key Id of the key') //OPTIONNAL
  ->set_secret_key('the_secret')
  ->build();
```

- **The private key is a PEM file** :
```PHP

$client->keysManager()
  ->use_for_encDec(),
  ->set_kid('The key Id of the key') //OPTIONNAL
  ->set_private_key_pem_file('/path/to/privateKey.pem')
  ->build();
```

- **Use of a P12 certificate** :
```PHP

$client->keysManager()
  ->use_for_encDec(),
  ->set_kid('The key Id of the key') //OPTIONNAL
  ->set_p12_file('/path/to/certificate.pfx')
  ->build();
```

### UserInfo response encrypted (JWE)
If the userInfo response is not a Nested JWT, but: 
- a JWE with a json payload : [do like for a Nested JWT](#nested-jwt)
- a JWS : Nothing to do 
- a JSON : Nothing to do

## Utils

### Generate a key pair

You can use openssl if you prefer. The library offers you the possibility to generate one.

**With the library :**

```PHP
// RSA
  $RsaKey = Svgta\OidcLib\OidcUtils::genRSAKey();
  // default 2048 length. To change length : Svgta\OidcUtils::genRSAKey(4096);

// EC
  $ECKey = Svgta\OidcLib\OidcUtils::genEcKey();
  // default curve P-256. To change curve : Svgta\OidcUtils::genEcKey('P-521');
```

The result is an array given the private and public key in two formats : 
- JWK
- PEM

**Example of a response** :
```shell
Array
(
    [JWK] => Array
        (
            [privateKey] => {"kty":"EC","crv":"P-256","d":"2t8bH5rd-VvD_ZuOKIOQXz5UymqMTvnX4gsOTPEzwW8","x":"D2XFGdEmpygLSqqn5SMXeR740smRBfULJet3hzkUZ-Y","y":"Mkis4wh5Ei1ccN3YpgpNeHXtgDXFcaLDlOYTgj-kgwc"}
            [publicKey] => {"kty":"EC","crv":"P-256","x":"D2XFGdEmpygLSqqn5SMXeR740smRBfULJet3hzkUZ-Y","y":"Mkis4wh5Ei1ccN3YpgpNeHXtgDXFcaLDlOYTgj-kgwc"}
        )

    [PEM] => Array
        (
            [privateKey] => -----BEGIN EC PRIVATE KEY-----
MHcCAQEEINrfGx+a3flbw/2bjiiDkF8+VMpqjE751+ILDkzxM8FvoAoGCCqGSM49
AwEHoUQDQgAED2XFGdEmpygLSqqn5SMXeR740smRBfULJet3hzkUZ+YySKzjCHkS
LVxw3dimCk14de2ANcVxosOU5hOCP6SDBw==
-----END EC PRIVATE KEY-----

            [publicKey] => -----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAED2XFGdEmpygLSqqn5SMXeR740smR
BfULJet3hzkUZ+YySKzjCHkSLVxw3dimCk14de2ANcVxosOU5hOCP6SDBw==
-----END PUBLIC KEY-----

        )

)

```

**With openssl**

```shell
# RSA
openssl genrsa -out key.pem 2048
openssl rsa -in key.pem -outform PEM -pubout -out public.pem

# EC
openssl ecparam -name prime256v1 -genkey -noout -out key.pem
openssl ec -in key.pem -pubout -out public.pem
```

---

### Generate an UUID

```PHP
$uuid = Svgta\OidcLib\OidcUtils::genUUID();
```
**Response**
```shell
uuid: dd5c827b-9c8a-4831-913e-f5cbec7195c4
```

---

### Generate a security key
By default, the key generated is 512 bits long. You can change it if needed.

```PHP
$key_1 = Svgta\OidcLib\OidcUtils::randomString();
$key_2 = Svgta\OidcLib\OidcUtils::randomString(1024);
$key_3 = Svgta\OidcLib\OidcUtils::randomString(64);
```

**Response**
```shell
key_1: 0gJfbQNHzJu4V2qEpz3JEklKujYrnhnd087vScNw28jUWvMq6ZUePx6jWClZq0A98oSjl9uH2m3cDiP-XdNGrQ
key_2: h0V3PmKCU5m_OupHR4g8zldCAnpwo-CcGLtCbq6iHEJnGMo0LqRIZ4j3az-5rK-kreBfrzZ4Zmcp41s5fhWFUC_GiGHRVX_azt6VNCE8KsYPX7FjpgWSg00V8k92z7ovDFaX4eFVmWzbOtxIDyK7f8cJ46x9B6Q2O1jttZlSRf4
key_3 : wlTzyVZEC7M
```

---

### Get informations of a certificate
The certificate must be in PEM format. The result is a stdClass object. Two méthods : 
* *getCertInfo* : to get from a variable
* *getCertInfoFile* : to get from a file

Get informations from a varaiable : 
```PHP
  $cert = <<<EOD
  -----BEGIN CERTIFICATE-----
// Certificate informations to PEM format
-----END CERTIFICATE-----
  EOD;
  $res = \Svgta\OidcLib\OidcUtils::getCertInfo($cert);
```

Get informations from a file : 
```PHP
  $path = '../pathTotheCertDir/myCert.crt';
  $res = \Svgta\OidcLib\OidcUtils::getCertInfoFile($path);
```

Response example :
```shell
stdClass Object
(
    [kty] => RSA
    [n] => s7npv4N-zt7XkCy3uCYkH38RYM-...
    [e] => AQAB
    [x5c] => Array
        (
            [0] => MIIDhzCCAm+gAwIBAgIEW66...
        )

    [x5t] => slZpLDjRxb86V8SqKHPl8KrRDII
    [x5t#256] => _pWwqSqIPbsaYBkQXCZzzOBcSEuGXJBymHgocLQlixU
)
```

---

### Verify if a string is a json

The response is a boolean.

```PHP
$isJson = Svgta\OidcLib\Oidcutils::isJson('My string');
// false;
$isJson = Svgta\OidcLib\Oidcutils::isJson('{"key": "first", "value": "test"}');
// true;
```





