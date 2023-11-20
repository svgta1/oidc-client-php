# Nested JWT

A Nested JWT is a JWT signed before encryption (a JWS in a JWE).

The library can automatically deal with Nested JWT for _userinfo response_ and _id\_token_. But, the library must known the _private key_ or the _secret_ to be used to decrypt the token received. It's has been defined with the OP.

To verify the JWS, the library use the _client\_secret_ or the OP _jwks\_uri_ like the process of a JWS _id\_token_.

In the examples, you have instantiate $client like seen before. You must set the parameters below before calling _tokens_ methods or _userInfo_ method

Cases :

* **The key to be used is the client\_secret** : You have nothing to do
* **The key is a shared key (secret)** :

```php

$client->keysManager()
  ->use_for_encDec(),
  ->set_kid('The key Id of the key') //OPTIONNAL
  ->set_secret_key('the_secret')
  ->build();
```

* **The private key is a PEM file** :

```php

$client->keysManager()
  ->use_for_encDec(),
  ->set_kid('The key Id of the key') //OPTIONNAL
  ->set_private_key_pem_file('/path/to/privateKey.pem')
  ->build();
```

* **Use of a P12 certificate** :

```php

$client->keysManager()
  ->use_for_encDec(),
  ->set_kid('The key Id of the key') //OPTIONNAL
  ->set_p12_file('/path/to/certificate.pfx')
  ->build();
```
