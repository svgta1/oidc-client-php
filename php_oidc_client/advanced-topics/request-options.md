# Request options

All the options depends on guzzle capabilities. See the [guzzle doc](https://docs.guzzlephp.org/en/stable).

* If you need to use a proxy to access to the OP (see the doc [guzzle](https://docs.guzzlephp.org/en/stable/request-options.html#proxy)) :&#x20;

```php
$client = new Svgta\OidcClient\init();
//...
$client->request->setHttpProxy('http://proxyUri:proxyPort');
$client->request->setHttpsProxy('http://proxyUri:proxyPort');
//the proxy is a string
$client->request->setNoProxy(['.myDomain', '.myOtherDomain']);
// the list is an array of domains that don't use proxy if http proxy and https proxy are set
```

* No verify TLS :&#x20;

```php
$client->request->verifyTls(false);
```

* Add SSL/TLS key :&#x20;

```php
$client->request->setCert('pathToFile');
//OR
$client->request->setCert(['pathToFile', 'password']);
```

* Add other guzzle options :&#x20;

```php
$client->request->addOtherParam('debug', true);
```

