# Verify if json

You can veritfy if a string is a json. The response is a boolean.

```php
use Svgta\Lib\Utils;

$isJson = Utils::isJson('My string');
// false;
$isJson = Utils::isJson('{"key": "first", "value": "test"}');
// true;
```
