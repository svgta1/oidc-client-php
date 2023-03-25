<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';
$jwt = Svgta\OidcJWT::gen_none_jwt('test', 'https://localhost/test')->signPayload();
$res = Svgta\OidcJWT::parseJWS($jwt);
unset($res['ressource']);
print_r($res);

$jwt = Svgta\OidcJWT::gen_client_secret_jwt('password1326855fdfdsfskkkfdsoieppamskkvds:cdskkfdsfkdsfsdo', 'test', 'https://localhost/test')->signPayload();
$res = Svgta\OidcJWT::parseJWS($jwt);
unset($res['ressource']);
print_r($res);

$key = Svgta\OidcUtils::genRsaKey();
$pKey = [
  'pem' => $key['PEM']['privateKey'],
  'pwd' => null,
  'kid' => Svgta\OidcUtils::genUUID(),
];
$jwt = Svgta\OidcJWT::gen_private_key_jwt($pKey, 'test', 'https://localhost/test')->signPayload();
$res = Svgta\OidcJWT::parseJWS($jwt);
unset($res['ressource']);
print_r($res);

$key = Svgta\OidcUtils::genEcKey();
$pKey = [
  'pem' => $key['PEM']['privateKey'],
  'pwd' => null,
  'kid' => Svgta\OidcUtils::genUUID(),
];
$jwt = Svgta\OidcJWT::gen_private_key_jwt($pKey, 'test', 'https://localhost/test')->signPayload();
$res = Svgta\OidcJWT::parseJWS($jwt);
unset($res['ressource']);
print_r($res);
