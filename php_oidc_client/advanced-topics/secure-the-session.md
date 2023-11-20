# Secure the session

To secure datas in your session (RECOMMENDED) :&#x20;

```php
$client = new Svgta\OidcClient\init();
//...
$client->setSessionKey('aRealSecureKey'); 
//...
```

The datas are encrypted in a JWE Format.
