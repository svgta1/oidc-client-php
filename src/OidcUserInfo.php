<?php
namespace Svgta\OidcClient;
use Svgta\OidcClient\OidcException as Exception;
use Svgta\Lib\Session;
use Svgta\Lib\Utils;
use Svgta\Lib\JWTVerifyTrait;
use Svgta\Lib\JWT;

class OidcUserInfo
{
  use JWTVerifyTrait;
  private $session = null;
  private $request = null;
  private $access_token = null;
  private $id_token = null;
  private $client_id = null;
  private $client_secret = null;

  public function __construct(string $client_id, OidcRequest $request, ?string $access_token = null, ?string $id_token = null, Session $session, ?string $client_secret = null){
    Utils::setDebug(__CLASS__, __FUNCTION__);
    $this->session = $session;
    if(!is_null($access_token) && !is_string($access_token))
      throw new Exception('Bad access_token type');
    if(is_null($access_token)){
      $tokens = $this->session->get('tokens');
      if(is_null($tokens))
        throw new Exception('access_token not set');
      if(!isset($tokens["access_token"]))
        throw new Exception('access_token not set');
      $access_token = $tokens['access_token'];
    }
    if(is_null($id_token)){
      $tokens = $this->session->get('tokens');
      if(isset($tokens["id_token"]))
        $id_token = $tokens['id_token'];
    }
    $this->access_token = $access_token;
    $this->id_token = $id_token;
    $this->request = $request;
    $this->client_id = $client_id;
    $this->client_secret = $client_secret;
  }

  private function get_JWT_info($jwt): array{
    $header = JWT::getJWTHeader($jwt);
    if(isset($header['enc'])){
      try{
        $nested = $this->ctrlJWT_nested($jwt);
        $payload = $nested['payload'];
        $alg = $nested['header']['alg'];
      }catch(\Throwable $t){
        $payload = $this->ctrlJWT_enc($jwt);
        $alg = null;
      }

    }else{
      $parse = JWT::parseJWS($jwt);
      $payload = $parse['payload'];
      $alg = $parse['header']['alg'];
      $this->ctrlJWT_sign($parse['ressource'], $alg, $jwt);
    }

    return [
      'payload' => $payload,
      'alg' => $alg,
    ];
  }

  public function get(): array{
    $res = $this->request->userInfo($this->access_token);
    $ctrlSub = false;
    $subId = null;
    if(!is_null($this->id_token)){
      $ctrlSub = true;
      $id_token_info = $this->get_JWT_info($this->id_token);
      if(!isset($id_token_info['payload']['sub']))
        throw new Exception('sub claim is required in id_token');
      $subId = $id_token_info['payload']['sub'];
    }
    if(is_array($res)){
      if($ctrlSub && !isset($res['sub']))
        throw new Exception('sub claim required in userInfo response');
      if($ctrlSub && ($res['sub'] != $subId))
        throw new Exception('Bad sub value');
      Utils::setDebug(__CLASS__, __FUNCTION__, $res);
      return $res;
    }

    $parseUserInfo = $this->get_JWT_info($res);
    $payload = $parseUserInfo['payload'];
    if($ctrlSub && !isset($payload['sub']))
      throw new Exception('sub claim required in userInfo response');
    if($ctrlSub && ($payload['sub'] != $subId))
      throw new Exception('Bad sub value');

    $alg = $parseUserInfo['alg'];
    if(is_null($alg))
      return $payload;

    $this->ctrlJWT_sign($parseUserInfo['ressource'], $alg, $res);
    $this->ctrlJWT_iss($payload);
    $this->ctrlJWT_aud($payload);

    Utils::setDebug(__CLASS__, __FUNCTION__, $payload);
    return $payload;
  }
}
