# Authorization flow

By default, the library use the **code** flow. You can change it if needed in the Authorization process

```php
//...
$client = new Svgta\OidcClient\init();
//...
$auth = $client->authorization($callback_url);
$auth->set_response_type('code id_token');
// value MUST be one of the follow :
// - code (code flow)
// - id_token (implicit flow)
// - token id_token (implicit flow)
// - code token (hybrid flow)
// - code id_token (hybrid flow)
// - code token id_token (hybrid flow)
```

You can use PKCE :&#x20;

```php
$auth->set_code_challenge_method('S256');
//Value are S256 or plain

```

Other options, details on the [OIDC specs](https://openid.net/specs/openid-connect-core-1\_0.html#AuthRequest):&#x20;

```php
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
