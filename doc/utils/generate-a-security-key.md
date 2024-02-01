# Generate a security key

By default, the key generated is 512 bits long. You can change it if needed.

```php
use Svgta\Lib\Utils;
$key_1 = Utils::randomString();
$key_2 = Utils::randomString(1024);
$key_3 = Utils::randomString(64);
```

**Response**

```shell
key_1 : 0gJfbQNHzJu4V2qEpz3JEklKujYrnhnd087vScNw28jUWvMq6ZUePx6jWClZq0A98oSjl9uH2m3cDiP-XdNGrQ
key_2 : h0V3PmKCU5m_OupHR4g8zldCAnpwo-CcGLtCbq6iHEJnGMo0LqRIZ4j3az-5rK-kreBfrzZ4Zmcp41s5fhWFUC_GiGHRVX_azt6VNCE8KsYPX7FjpgWSg00V8k92z7ovDFaX4eFVmWzbOtxIDyK7f8cJ46x9B6Q2O1jttZlSRf4
key_3 : wlTzyVZEC7M
```
