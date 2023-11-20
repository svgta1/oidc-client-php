# Verify if json

You can veritfy if a string is a json. The response is a boolean.

```php
use Svgta\OidcLib\OidcUtils;

$isJson = Oidcutils::isJson('My string');
// false;
$isJson = Oidcutils::isJson('{"key": "first", "value": "test"}');
// true;
```
