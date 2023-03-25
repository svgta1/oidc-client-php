<?php
namespace Svgta;
use Svgta\OidcException as Exception;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Core\JWK;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\JWS;

class OidcJWT
{
  private static $expJwtForAuth = 300;
  private static $default_sig_alg = [
    'RSA' => 'RS256',
    'EC' => [
      'P-256' => 'ES256',
      'P-384' => 'ES384',
      'P-521' => 'ES512',
    ],
    'oct' => 'HS256',
    'none' => 'none',
  ];
  private $client_id = null;
  private $endpoint = null;
  private $JWK = null;
  private $sigAlg = null;
  private $JWT = null;

  public static $alg_sig_accepted = [
    'RSA' => [
      'RS256',
      'RS384',
      'RS512',
      'PS256',
      'PS384',
      'PS512'
    ],
    'EC' => [
      'ES256',
      'ES384',
      'ES512',
    ],
    'oct' => [
      'HS256',
      'HS384',
      'HS512'
    ],
    'none' => ['none']
  ];

  public static function parseJWS(string $jwt): array{
    $serializerManager = new JWSSerializerManager([
        new CompactSerializer(),
    ]);
    $jws = $serializerManager->unserialize($jwt);
    return [
      'header' => $jws->getSignature(0)->getProtectedHeader(),
      'payload' => json_decode($jws->getPayload(), true),
      'ressource' => $jws,
    ];
  }

  private function __construct(JWK $JWK){
    $this->JWK = $JWK;
    $this->setAlg();
  }

  private function getVerifier(): JWSVerifier{
    $algorithmManager = $this->getAlgoManager();
    $jwsVerifier = new JWSVerifier(
      $algorithmManager
    );
    return $jwsVerifier;
  }
  public function verifyJWSWithKey(JWS $jws){
    $verifier = $this->getVerifier();
    $isVerified = $verifier->verifyWithKey($jws, $this->JWK, 0);
    if(!$isVerified)
      throw new Exception('JWK signature not verified');
  }
  public function verifyJWSWithKeysSet(JWS $jws){
    $verifier = $this->getVerifier();
    $isVerified = $verifier->verifyWithKeySet($jws, $this->JWK, 0);
    if(!$isVerified)
      throw new Exception('JWK signature not verified');
  }

  public static function set_sign_params(string $alg, $client_secret = null, array $keySet = []): self{
    $key = null;
    if($alg == 'none')
      $key = JWKFactory::createNoneKey();
    if(substr($alg, 0, 2) == 'HS')
      $key = JWKFactory::createFromSecret($client_secret,[
        'use' => 'sig',
      ]);
    if(is_null($key))
      $key = JWKSET::createFromKeyData($keySet);
    $res = new self($key);
    $res->setAlg($alg);
    return $res;
  }

  private function set_client_id(string $client_id){
    $this->client_id = $client_id;
  }

  private function set_endpoint(string $endpoint){
    $this->endpoint = $endpoint;
  }

  private function getAlgoManager(): AlgorithmManager{
    $classAlg = $this->sigAlg;
    if($classAlg == 'none')
      $classAlg = 'None';
    $class = 'Jose\\Component\\Signature\\Algorithm\\' . $classAlg;
    $algorithmManager = new AlgorithmManager([
      new $class(),
    ]);
    return $algorithmManager;
  }

  public function signPayload(): string{
    $payload = self::getPayloadForJwtAuth($this->client_id, $this->endpoint);
    $algorithmManager = $this->getAlgoManager();
    $jwsBuilder = new JWSBuilder($algorithmManager);
    $options = [
      'alg' => $this->sigAlg,
      'typ' => 'JWT'
    ];
    if($this->JWK->has('kid'))
      $options['kid'] = $this->JWK->get('kid');
    $jws = $jwsBuilder
      ->create()
      ->withPayload(json_encode($payload))
      ->addSignature($this->JWK, $options)
      ->build();
    $serializer = new CompactSerializer();
    $token = $serializer->serialize($jws, 0);
    return $token;
  }

  public function setAlg(string $alg = null){
    if(!$this->JWK->has('kty'))
      throw new Exception('Type of the key not set');
    $type = $this->JWK->get('kty');

    if(!isset(self::$alg_sig_accepted[$type]))
      throw new Exception('Type of the key not known');

    if($type == 'EC')
      $alg = self::$default_sig_alg[$type][$this->JWK->get('crv')];

    if(is_null($alg))
      $alg = self::$default_sig_alg[$type];
    if(!in_array($alg, self::$alg_sig_accepted[$type]))
      throw new Exception('Algorithm not allowed for this key');

    $this->sigAlg = $alg;
  }

  public static function gen_none_jwt(string $client_id, string $endpoint){
    $key = JWKFactory::createNoneKey();
    $res = new self($key);
    $res->set_client_id($client_id);
    $res->set_endpoint($endpoint);
    return $res;
  }
  public static function gen_client_secret_jwt(string $client_secret, string $client_id, string $endpoint): self{
    $key = JWKFactory::createFromSecret($client_secret,[
      'use' => 'sig',
    ]);
    //$key = new JWK([
    //  'kty' => 'oct',
    //  'k' => $client_secret,
    //]);
    $res = new self($key);
    $res->set_client_id($client_id);
    $res->set_endpoint($endpoint);
    return $res;
  }
  public static function gen_private_key_jwt(array $privateKey, string $client_id, string $endpoint): self{
    $options = [
      'use' => 'sig',
    ];
    if(isset($privateKey['kid']))
      $options['kid'] = $privateKey['kid'];

    $key = JWKFactory::createFromKey(
      $privateKey['pem'],
      $privateKey['pwd'],
      $options
    );
    $res = new self($key);
    $res->set_client_id($client_id);
    $res->set_endpoint($endpoint);
    return $res;
  }

  private static function getPayloadForJwtAuth(string $client_id, string $endpoint): array{
    $ar = [
      'iss' => $client_id,
      'sub' => $client_id,
      'aud' => $endpoint,
      'jti' => OidcUtils::genUUID(),
      'exp' => time() + self::$expJwtForAuth,
      'iat' => time(),
      'nbf' => time(),
    ];
    return $ar;
  }
}
