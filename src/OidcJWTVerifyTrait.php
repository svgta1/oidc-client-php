<?php
namespace Svgta;
use Svgta\OidcException as Exception;
use Jose\Component\Signature\JWS;

trait OidcJWTVerifyTrait
{
  private function ctrlJWT_sign(JWS $ressource, string $alg): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    $alg_sig_accepted = OidcJWT::$alg_sig_accepted;
    $algAccepted = [];
    foreach($alg_sig_accepted as $k => $v){
      $algAccepted = array_merge($algAccepted, $v);
    }
    if(!in_array($alg, $algAccepted))
      throw new Exception('The JWT alg is not accepted');

    $keySet = [];
    $algType = substr($alg, 0, 2);
    if($algType != 'HS' && $alg != 'none'){
      $keySet = $this->request->jwk_uri();
      OidcJWT::set_sign_params($alg, null, $keySet)->verifyJWSWithKeysSet($ressource);
    }else{
      OidcJWT::set_sign_params($alg, $this->client_secret, [])->verifyJWSWithKey($ressource);
    }
  }

  private function ctrlJWT_at_hash(array $payload, ?string $access_token = null, string $alg): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    if(isset($payload['at_hash'])){
      if(!OidcUtils::ctrlHash($access_token, $payload['at_hash'], $alg))
        throw new Exception('Bad payload at_hash value');
    }
  }

  private function ctrlJWT_c_hash(array $payload, string $alg): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    if(isset($payload['c_hash'])){
      if(!OidcUtils::ctrlHash($this->code, $payload['c_hash'], $alg))
        throw new Exception('Bad payload c_hash value');
    }
  }

  private function ctrlJWT_time(array $payload): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    $ts = time();
    OidcUtils::ctrlTypeTime($payload['exp']);
    OidcUtils::ctrlTypeTime($payload['iat']);
    if(isset($payload['nbf']))
      OidcUtils::ctrlTypeTime($payload['nbf']);

    if($payload['exp'] < $ts)
      throw new Exception('Id_token expired');
    if($payload['iat'] > $ts)
      throw new Exception('Id_token iat greater than timestamp');
    if(isset($payload['nbf']) && $payload['nbf'] > $ts)
      throw new Exception('Id_token not usable');
  }

  private function ctrlJWT_nonce(array $payload): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    if(isset($this->authParams['nonce'])){
      if(!isset($payload['nonce']))
        throw new Exception('The OP must generate authParams nonce claim');
      if($payload['nonce'] !== $this->authParams['nonce'])
        throw new Exception('Bad nonce value');
    }

  }

  private function ctrlJWT_iss(array $payload): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    $iss = $this->session->get('FI_PARAMS')->issuer;
    if($payload['iss'] !== $iss)
      throw new Exception('Bad issuer value');
  }

  private function ctrlJWT_aud(array $payload): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    if(gettype($payload['aud']) !== 'string' && gettype($payload['aud']) !== 'array')
      throw new Exception('Bad audience claim type');
    if(is_string($payload['aud']))
      if($payload['aud'] !== $this->client_id)
        throw new Exception('Bad audiance value');
    if(is_array($payload['aud']))
      if(!in_array($this->client_id, $payload['aud']))
        throw new Exception('Bad audiance value');
  }
}