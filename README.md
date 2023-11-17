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

The documentation is [here](https://svgtas-organization.gitbook.io/php-oidc-client/)





