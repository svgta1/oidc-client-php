<?php
namespace Svgta;
use Svgta\OidcException as Exception;

class OidcUserInfo
{
  use OidcJWTVerifyTrait;
  private $session = null;
  private $request = null;
  private $access_token = null;
  private $id_token = null;
  private $client_id = null;

  public function __construct(string $client_id, OidcRequest $request, $access_token = null, $id_token = null){
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    $this->session = new OidcSession();
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
  }

  public function get(): array{
    $res = $this->request->userInfo($this->access_token);
    $parseIdToken = OidcJWT::parseJWS($this->id_token);
    if(!isset($parseIdToken['payload']['sub']))
      throw new Exception('sub claim is required in id_token');
    $subId = $parseIdToken['payload']['sub'];

    if(is_array($res)){
      if(!isset($res['sub']))
        throw new Exception('sub claim required in userInfo response');
      if($res['sub'] != $subId)
        throw new Exception('Bad sub value');
      OidcUtils::setDebug(__CLASS__, __FUNCTION__, $res);
      return $res;
    }

    $parseUserInfo = OidcJWT::parseJWS($res);
    $payload = $parseUserInfo['payload'];
    if(!isset($payload['sub']))
      throw new Exception('sub claim required in userInfo response');
    if($payload['sub'] != $subId)
      throw new Exception('Bad sub value');

    $alg = $parseUserInfo['header']['alg'];
    if(isset($parseUserInfo['header']['enc']))
      throw new Exception('Encrypted userinfo not supported');

    $this->ctrlJWT_sign($parseUserInfo['ressource'], $alg);
    $this->ctrlJWT_iss($payload);
    $this->ctrlJWT_aud($payload);

    OidcUtils::setDebug(__CLASS__, __FUNCTION__, $payload);
    return $payload;
  }
}
