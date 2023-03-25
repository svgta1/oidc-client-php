<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

Svgta\OidcClient::setLogLevel(LOG_DEBUG);
$client = new Svgta\OidcClient(
  'https://accounts.google.com/.well-known/openid-configuration',
  'Your_client_id',
  'Your_client_secret'
);

/*auth = $client->authorization('https://your_callback_uri');
$auth->set_response_type('code');
$auth->set_state();
$auth->set_nonce();
print_r($auth->getUri());
*/
//

//$tokenRes = $client->token();
//$tokenRes->client_credentials();
//$tokens = $tokenRes->password_grant('toto', 'tata');
//$tokens = $tokenRes->get_tokens();

$access_token = 'ya29.a0Ael9sCO637t0Blrxy7OJzqKedMx3vydeSYD4EoncE7osW7ruP7tR2UIbobmqnkWq02SbMyvc5U9cTA6kOoIDGA2KEViHcjmq4dF5a5vaF3oxDJG6eZBBjdROTQ6kqUWvKRkKsLoo8e1BhJ3ihwaxQsDxOiqiK30xPplAAZYaCgYKAZMSARESFQF4udJhLDBRmgAw4HJtTOu_Tga7NA0174';
$id_token = 'eyJhbGciOiJFUzUxMiIsInR5cCI6IkpXVCIsImtpZCI6IjcwZjg4ZDVhLTJjZTctNDkyNy04OWMyLTJhYTM2M2U0YWJlMSJ9.eyJhdF9oYXNoIjoiNW5MbUdFb3hsa2Q2R3A0dExBVXpWSjlaN3BuU3k3NG9iWU5MQ096bjhXVSIsInN1YiI6InRlc3QiLCJpc3MiOiJodHRwczpcL1wvbWVzaGlzdG9pcmVzLmZyIiwiYXVkIjoidGVzdCIsImF6cCI6InRlc3QiLCJleHAiOjE2Nzk2ODE1MDcsImlhdCI6MTY3OTY3NzkwNywibmJmIjoxNjc5Njc3OTA3fQ.AehHN98PlTprMVUNVujTp-V5exJmr5JvXQuH7JEMLANYnnz7v6ZRE946t3A7Apw2bNdkGedFlpczO4AFkgN2GmszATjkvG1FnV1WzO32rseA369lMSEB1knRTXMf1CFIjk-0aQAyKQ6mXtmtIT85a3cQZ1uWeDdfJv7cwWwYl_nzE39i';

$userInfo = $client->userInfo($access_token, $id_token);

print_r($userInfo);
