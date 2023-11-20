# UserInfo

The _userinfo\_endpoint_ need the _access\_token_. If it's in session, you don't need to give it back.

The _id\_token_ is required to verify the _sub_ claim. If it's in session, you don't need to give it back.

The library support the response in _json_ format and _jwt_ (jwt signed by the OP with a key known in it's _jwks\_uri_ endpoint or signed with the _client\_secret_).

```php
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

> :warning: _userinfo\_endpoint_ not set
>
> Some OP don't give an _userinfo\_endpoint_ (Dropbox is an example). If the contents of the _id\_token_ is enough for you, you can get the result of the _payload_
>
> ```php
> ...
> $tokenRes = $client->token();
> $tokens = $tokenRes->get_tokens();
> $payload = $tokenRes->get_id_token_payload(); //result is an array
> ```
>
> If you call _$client->userInfo()_ but the OP don't have the _userinfo\_endpoint_ set, you will get an _Svgta\OidcException_
