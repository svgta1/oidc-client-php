# Logout

Based on OpenID Connect Session Management 1.0 - [draft 17](https://openid.net/specs/openid-connect-session-1\_0-17.html).

The OP must have the endpoint _end\_session\_endpoint_. It's recommended to give the _id\_token_ in the request. If not set, the library tries to find it in its session.

The _redirect\_uri_ can be used by the OP to redirect the user to the page defined. The _redirect\_uri_ has to be registred in the OP configuration.

Basic :

```php
$client = new Svgta\OidcClient\init(
  'https://id.provider.com/.well-known/openid-configuration'
);

$id_token = 'The_Id_Token_You_Get'; //format jwt string
$redirect_uri = 'https://yourApp.tld/logoutCallback';

$client->logout($id_token, $redirect_uri);
// $id_token and $redirecti_uri are optionals. If id_token is not given, the library try to find it in the session.
// if you don't give id_token but give redirect_uri : $client->logout(null, $redirect_uri);
```
