<?php
namespace Svgta;
use Svgta\OidcException as Exception;
use Jose\Component\KeyManagement\JWKFactory;

class OidcKeys
{
  private static $sign_key_private = null;
  private static $sign_key_public = null;
  private static $enc_key_private = null;
  private static $enc_key_public = null;

  public static function get_private_key_sign(): OidcJWT{
    return $sign_key_private;
  }

  public static function get_public_key_sign(): OidcJWT{
    return $sign_key_public;
  }

  public static function get_private_key_enc(): OidcJWT{
    return $enc_key_private;
  }

  public static function get_public_key_enc(): OidcJWT{
    return $enc_key_public;
  }

  private $private_pem = null;
  private $public_pem = null;
  private $p12 = null;
  private $x509 = null;
  private $p12Pwd = null;
  private $pemPwd = null;
  private $kid = null;
  private $useSig = false;
  private $useEnc = false;
  private $useX5t = false;

  public function __construct(){

  }

  public function use_x5t(): self{
    $clone = clone $this;
    $clone->useX5t = true;
    return $clone;
  }

  public function use_for_encDec(): self{
    if($this->useSig)
      throw new Exception('Can not been used for encryption and signature at the same time');
    $clone = clone $this;
    $clone->useEnc = true;
    return $clone;
  }

  public function use_for_signVerify(): self{
    if($this->useEnc)
      throw new Exception('Can not been used for encryption and signature at the same time');
    $clone = clone $this;
    $clone->useSig = true;
    return $clone;
  }

  public function set_private_key_pem(string $pem, ?string $paswword = null): self{
    if(!is_null($this->p12))
      throw new Exception('Only one of p12 or private key can been used');
    $clone = clone $this;
    $clone->private_pem = $pem;
    $clone->pemPwd = $password;

    return $clone;
  }

  public function set_public_key_pem(string $pem): self{
    if(!is_null($this->p12) || !is_null($this->x509))
      throw new Exception('Only one of p12, X509 or public key can been used');
    $clone = clone $this;
    $clone->public_pem = $pem;
    $clone->pemPwd = $password;

    return $clone;
  }

  public function set_private_key_pem_file(string $path, ?string $paswword = null): self{
    $pem = \file_get_contents($path);
    return $this->set_private_key_pem($pem, $password);
  }

  public function set_public_key_pem_file(string $path): self{
    $pem = \file_get_contents($path);
    return $this->set_public_key_pem($pem);
  }

  public function set_x509_file(string $path): self{
    $x509 = \file_get_contents($path);
    return $this->set_x509($x509);
  }

  public function set_x509(string $x509): self{
    if(!is_null($this->p12) || !is_null($this->public_pem))
      throw new Exception('Only one of p12, X509 or public key can been used');
    $clone = clone $this;
    $clone->x509 = $x509;
    return $clone;
  }

  public function set_p12_file(string $path, ?string $paswword = null): self{
    if(!is_null($this->private_pem))
      throw new Exception('Only one of p12 or private key can been used');
    if(!is_null($this->x509) || !is_null($this->public_pem))
      throw new Exception('Only one of p12, X509 or public key can been used');
    $clone = clone $this;
    $clone->p12 = $path;
    $clone->p12Pwd = $password;
    return $clone;
  }

  public function set_kid(string $kid): self{
    $clone = clone $this;
    $clone->kid = $kid;
    return $clone;
  }

  public function build(): void{
    if(!$this->useSig && !$this->useEnc)
      throw new Exception('You must specify the use of the key');
    $options = [];
    if($this->useSig)
      $options['use'] = "sig";
    if($this->useEnc)
      $options['use'] = "enc";
    if(!is_null($this->kid))
      $options['kid'] = $this->kid;

    if(!is_null($this->p12)){
      $key = JWKFactory::createFromPKCS12CertificateFile(
        $this->p12, // The filename
        $this->p12Pwd,
        $params
      );
    }
  }
