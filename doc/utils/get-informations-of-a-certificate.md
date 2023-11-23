# Get informations of a certificate

The certificate must be in PEM format. The result is a stdClass object. Two méthods :

* _getCertInfo_ : to get from a variable
* _getCertInfoFile_ : to get from a file

Get informations from a variable :

```php
use Svgta\OidcLib\OidcUtils;

  $cert = <<<EOD
  -----BEGIN CERTIFICATE-----
// Certificate informations to PEM format
-----END CERTIFICATE-----
  EOD;
  $res = OidcUtils::getCertInfo($cert);
```

Get informations from a file :

```php
  $path = '../pathTotheCertDir/myCert.crt';
  $res = OidcUtils::getCertInfoFile($path);
```

Response example :

```shell
stdClass Object
(
    [kty] => RSA
    [n] => s7npv4N-zt7XkCy3uCYkH38RYM-...
    [e] => AQAB
    [x5c] => Array
        (
            [0] => MIIDhzCCAm+gAwIBAgIEW66...
        )

    [x5t] => slZpLDjRxb86V8SqKHPl8KrRDII
    [x5t#256] => _pWwqSqIPbsaYBkQXCZzzOBcSEuGXJBymHgocLQlixU
)
```
