<?php

require dirname(__FILE__, 2) . '/vendor/autoload.php';
$client = new Svgta\OidcClient(
  'https://accounts.google.com/.well-known/openid-configuration',
  'my_client_id'
);
$auth = $client->authorization('https://test/com');
$auth->addScope('email profile');
$auth->set_state();
$auth->set_nonce();
$auth->set_code_challenge_method('S256');
//$auth->set_acr_values('toto');
//$auth->set_login_hint();
//$auth->set_id_token_hint();
//$auth->set_ui_locales();
//$auth->set_max_age();
//$auth->set_prompt();
//$auth->set_display();
//$auth->set_response_mode();
print_r($auth->getUri());
