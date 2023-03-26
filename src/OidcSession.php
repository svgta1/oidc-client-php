<?php
namespace Svgta;
use Svgta\OidcException as Exception;
use MI\MCE\pwdApi\utils\JWK as apiJWK;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;

class OidcSession
{
  private static $name = "SvgtaOidcClient";
  private static $encKey = null;

  const DEFAULT_ENCALG = 'A256KW';
  const DEFAULT_ENCENC = 'A256CBC-HS512';

  private function encrypt(string $str): string{
    $encAlg = self::DEFAULT_ENCENC;
    $alg = self::DEFAULT_ENCALG;
    $keyEncClass =  '\\Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\' . str_replace('-', '', $alg);
    $keyEncryptionAlgorithmManager = new AlgorithmManager([
      new $keyEncClass(),
    ]);
    $encClass = 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\' . str_replace('-', '', $encAlg);
    $contentEncryptionAlgorithmManager = new AlgorithmManager([
      new $encClass(),
    ]);
    $compressionMethodManager = new CompressionMethodManager([
      new Deflate(),
    ]);
    $jweBuilder = new JWEBuilder(
        $keyEncryptionAlgorithmManager,
        $contentEncryptionAlgorithmManager,
        $compressionMethodManager
    );
    $jwk = JWKFactory::createFromSecret(OidcSession::$encKey);
    $jwe = $jweBuilder
      ->create()
      ->withPayload($str)
      ->withSharedProtectedHeader([
        'alg' => $alg,
        'enc' => $encAlg,
      ])
      ->addRecipient($jwk)
      ->build();

    $serializer = new CompactSerializer();
    return $serializer->serialize($jwe, 0);
  }

  private function decrypt(string $jwe): ?string{
    list($header) = explode('.', $jwe);
    if(!$header = json_decode(OidcUtils::base64url_decode($header)))
      return null;
    if(!isset($header->alg) AND !isset($header->enc))
      return null;

    $alg = $header->alg;
    $enc = $header->enc;

    $keyEncClass =  '\\Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\' . str_replace('-', '', $alg);
    $keyEncryptionAlgorithmManager = new AlgorithmManager([
        new $keyEncClass(),
    ]);

    $encClass = 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\' . str_replace('-', '', $enc);
    $contentEncryptionAlgorithmManager = new AlgorithmManager([
        new $encClass(),
    ]);
    $compressionMethodManager = new CompressionMethodManager([
        new Deflate(),
    ]);
    $jweDecrypter = new JWEDecrypter(
        $keyEncryptionAlgorithmManager,
        $contentEncryptionAlgorithmManager,
        $compressionMethodManager
    );

    $jwk = JWKFactory::createFromSecret(OidcSession::$encKey);
    $serializerManager = new JWESerializerManager([
        new CompactSerializer(),
    ]);
    $jwe = $serializerManager->unserialize($jwe);
    if($jweDecrypter->decryptUsingKey($jwe, $jwk, 0))
      return $jwe->getPayload();
    return null;
  }

  public static function setSessionKey(string $key){
      self::$encKey = $key;
  }

  public function __construct(){
    if(session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if(!isset($_SESSION['SvgtaOidcClient']))
      $_SESSION[self::$name] = [];
  }

  public function put(string $key, mixed $value): void{
    $ser = serialize($value);
    if(is_null(OidcSession::$encKey))
      $_SESSION[self::$name][$key] = $ser;
    else
      $_SESSION[self::$name][$key] = $this->encrypt($ser);
  }

  public function get(string $key): mixed{
    if(isset($_SESSION[self::$name][$key])){
      $val = $_SESSION[self::$name][$key];
      if(is_null(OidcSession::$encKey)){
        $valAr = explode('.', $val);
        if(count($valAr) > 2){
          $head = OidcUtils::base64url_decode($valAr[0]);
          if(!OidcUtils::isJson($head))
            return unserialize($val);
          $headDec = json_decode($head);
          if(isset($headDec->alg) || isset($deadDec->enc))
            throw new Exception('You have to give the key of the session');
          return unserialize($val);
        }
        return unserialize($val);
      }
      $dec = $this->decrypt($val);
      if(is_null($dec))
        throw new Exception('The key of the session is wrong');
      return unserialize($dec);
    }
    return null;
  }

  public function delete(string $key): void{
    if(isset($_SESSION[self::$name][$key]))
      unset($_SESSION[self::$name][$key]);
  }

  public function clear(): void{
    $_SESSION[self::$name] = [];
  }
}
