<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';
$key = Svgta\OidcUtils::genRsaKey();
print_r($key); echo PHP_EOL;
$key = Svgta\OidcUtils::genEcKey();
print_r($key); echo PHP_EOL;
