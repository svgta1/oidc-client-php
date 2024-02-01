# Generate a key pair

You can use openssl if you prefer. The library offers you the possibility to generate one.

**With the library :**

```php
use Svgta\Lib\Utils;
// RSA
  $RsaKey = Utils::genRSAKey();
  // default 2048 length. To change length : Svgta\Utils::genRSAKey(4096);

// EC
  $ECKey = Utils::genEcKey();
  // default curve P-256. To change curve : Svgta\Utils::genEcKey('P-521');
```

The result is an array given the private and public key in two formats :

* JWK
* PEM

**Example of a response** :

```php
Array
(
    [JWK] => Array
        (
            [privateKey] => {"kty":"EC","crv":"P-256","d":"2t8bH5rd-VvD_ZuOKIOQXz5UymqMTvnX4gsOTPEzwW8","x":"D2XFGdEmpygLSqqn5SMXeR740smRBfULJet3hzkUZ-Y","y":"Mkis4wh5Ei1ccN3YpgpNeHXtgDXFcaLDlOYTgj-kgwc"}
            [publicKey] => {"kty":"EC","crv":"P-256","x":"D2XFGdEmpygLSqqn5SMXeR740smRBfULJet3hzkUZ-Y","y":"Mkis4wh5Ei1ccN3YpgpNeHXtgDXFcaLDlOYTgj-kgwc"}
        )

    [PEM] => Array
        (
            [privateKey] => -----BEGIN EC PRIVATE KEY-----
MHcCAQEEINrfGx+a3flbw/2bjiiDkF8+VMpqjE751+ILDkzxM8FvoAoGCCqGSM49
AwEHoUQDQgAED2XFGdEmpygLSqqn5SMXeR740smRBfULJet3hzkUZ+YySKzjCHkS
LVxw3dimCk14de2ANcVxosOU5hOCP6SDBw==
-----END EC PRIVATE KEY-----

            [publicKey] => -----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAED2XFGdEmpygLSqqn5SMXeR740smR
BfULJet3hzkUZ+YySKzjCHkSLVxw3dimCk14de2ANcVxosOU5hOCP6SDBw==
-----END PUBLIC KEY-----

        )

)

```

**With openssl**

```shell
# RSA
openssl genrsa -out key.pem 2048
openssl rsa -in key.pem -outform PEM -pubout -out public.pem

# EC
openssl ecparam -name prime256v1 -genkey -noout -out key.pem
openssl ec -in key.pem -pubout -out public.pem
```

***

####
