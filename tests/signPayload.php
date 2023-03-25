<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

/*
$RsaKey = Svgta\OidcUtils::genRSAKey();
$key = [
  'pem' => $RsaKey['PEM']['privateKey'],
  'pwd' => null
];
$JWK = Svgta\OidcJWT::gen_private_key_jwt($key, 'my_client_id', 'https://auth.endpoint.local');
print_r($JWK);
$JWK->setAlg('PS512');
$payload = $JWK->signPayload();
print_r($RsaKey['PEM']['publicKey']); echo PHP_EOL;
print_r($payload); echo PHP_EOL;
*/

$key = 'gfspom,gfomjgfogfdgojgfdml,gfdpm55.255gfspom,gfomjgfogfdgojgfdml,gfdpm55.255gfspom,gfomjgfogfdgojgfdml,gfdpm55.255';
$JWK = Svgta\OidcJWT::gen_client_secret_jwt($key, 'my_client_id', 'https://auth.endpoint.local');
//$JWK = Svgta\OidcJWT::gen_none_jwt('my_client_id', 'https://auth.endpoint.local');
print_r($JWK);
$JWK->setAlg('HS512');
$jws = $JWK->signPayload();
print_r($key); echo PHP_EOL;
print_r($jws); echo PHP_EOL;
print_r($JWK); echo PHP_EOL;

$parse = Svgta\OidcJWT::parseJWS($jws);
$alg = $parse['header']['alg'];
Svgta\OidcJWT::set_sign_params($alg, $key, [])->verifyJWSWithKey($parse['ressource']);
