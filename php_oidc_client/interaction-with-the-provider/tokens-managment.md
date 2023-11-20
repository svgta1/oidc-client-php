# Tokens managment

## **Information**

All tokens get by the differents methods are set in session.

The _id\_token_ must be a JWS (JWT signed by the OP with a key known in it's _jwks\_uri_ endpoint or signed with the _client\_secret_).

***

## **Flow code, implicit, hybrid**

Generaly used by the callback url after the authorization on the OP for code or hybrid.

Basic usage :

```php
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

***

## **Password grant**

The _Password grant_ flow should not be used. See explanation on : https://www.oauth.com/oauth2-servers/access-tokens/password-grant/

If your OP don't accept it, you can not used it.

Basic usage :

```php
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

***

## **Client credentials**

This flow is used when applications request an _access\_token_ to access their own resources.

Basic usage :

```php
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

***

## **Refresh token**

To get new _access\_token_ and _id\_token_. The refresh\_token must be send with the others tokens. Generaly, in the authorization flow, the scope _offline\_access_ must be used.

Basic usage :

```php
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

***

## **Introspect token**

The OP must have _introspection\_endpoint_ set.

> The instrospection endpoint is not defined in OpenId Connect Provider Metadata ([https://openid.net/specs/openid-connect-discovery-1\_0.html#ProviderMetadata](https://openid.net/specs/openid-connect-discovery-1\_0.html#ProviderMetadata)). You can add it with (it's an example) :
>
> ```php
>  $client->add_OP_info('introspection_endpoint', 'https://id.provider.com/intro');
> ```

Based on [rfc7662](https://www.rfc-editor.org/rfc/rfc7662), the token must be an _access\_token_ or a _refresh\_token_.

**Usage :**

```php
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

***

## **Revoke token**

Only _access\_token_ and _refresh\_token_ can be used.

The OP must have _revocation\_endpoint_ set.

> The revocation endpoint is not defined in OpenId Connect Provider Metadata ([https://openid.net/specs/openid-connect-discovery-1\_0.html#ProviderMetadata](https://openid.net/specs/openid-connect-discovery-1\_0.html#ProviderMetadata)). You can add it with (it's an example) :
>
> ```php
>  $client->add_OP_info('revocation_endpoint', 'https://id.provider.com/revoke');
> ```

**Usage :**

```php
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
