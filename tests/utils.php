<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';

$uuid = Svgta\OidcUtils::genUUID();
print_r($uuid); echo PHP_EOL;


$key = Svgta\OidcUtils::randomString();
$key1 = Svgta\OidcUtils::randomString(1024);
$key2 = Svgta\OidcUtils::randomString(64);

print_r($key); echo PHP_EOL;
print_r($key1); echo PHP_EOL;
print_r($key2); echo PHP_EOL;
