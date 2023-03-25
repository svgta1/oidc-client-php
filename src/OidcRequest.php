<?php
namespace Svgta;
use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use Svgta\OidcException as Exception;

class OidcRequest
{
  private $params = [];
  private $client = null;
  private $welcomeUrl = null;
  public static $FI_config = null;
  private $session = null;

  public function __construct($welcomeUrl = null){
    $this->welcomeUrl = $welcomeUrl;
    $this->client = new Client();
    $this->session = new OidcSession();
  }
  public function introspect_token(array $params){
    $endpoint = $this->ctrlParamsIntro();
    $params = array_merge($this->params, $params);
    try{
      $res = $this->client->request('POST', $endpoint, $params);
    }catch(\GuzzleHttp\Exception\ClientException $e){
      throw new Exception(json_encode([
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ]));
    }catch(\GuzzleHttp\Exception\RequestException $e){
      throw new Exception($e->getMessage());
    }
    $contentType = $res->getHeader('Content-Type')[0];
    if(preg_match('/^application\/json/', $contentType)){
      $ar = json_decode($res->getBody(), true);
      return $ar;
    }
    if(preg_match('/^application\/x-www-form-urlencoded/', $contentType)){
      $contents = urldecode($res->getBody()->getContents());
      $ar = [];
      foreach(explode('&', $contents) as $content){
        list($param, $value) = explode("=", $content);
        $ar[$param] = $value;
      }
      if(isset($ar['error']))
        throw new Exception(json_encode($ar));

      return $ar;
  }
  public function revocation_endpoint(array $params){
    $endpoint = $this->ctrlParamsRevoc();
    $params = array_merge($this->params, $params);
    try{
      $res = $this->client->request('POST', $endpoint, $params);
    }catch(\GuzzleHttp\Exception\ClientException $e){
      throw new Exception(json_encode([
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ]));
    }catch(\GuzzleHttp\Exception\RequestException $e){
      throw new Exception($e->getMessage());
    }
    $contentType = $res->getHeader('Content-Type')[0];
    if(preg_match('/^application\/json/', $contentType)){
      $ar = json_decode($res->getBody(), true);
      return $ar;
    }
    if(preg_match('/^application\/x-www-form-urlencoded/', $contentType)){
      $contents = urldecode($res->getBody()->getContents());
      $ar = [];
      foreach(explode('&', $contents) as $content){
        list($param, $value) = explode("=", $content);
        $ar[$param] = $value;
      }
      if(isset($ar['error']))
        throw new Exception(json_encode($ar));

      return $ar;
  }

  public function userInfo(string $access_token){
    $params = ['headers' => ['Authorization' => 'Bearer ' . $access_token]];
    $params = array_merge($this->params, $params);
    $endpoint = $this->ctrlParamsUserInfo();
    try{
      $res = $this->client->request('GET', $endpoint, $params);
    }catch(\GuzzleHttp\Exception\ClientException $e){
      throw new Exception(json_encode([
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ]));
    }catch(\GuzzleHttp\Exception\RequestException $e){
      throw new Exception($e->getMessage());
    }
    $contentType = $res->getHeader('Content-Type')[0];
    if(preg_match('/^application\/json/', $contentType)){
      $ar = json_decode($res->getBody(), true);
      return $ar;
    }
    if(preg_match('/^application\/jwt/', $contentType)){
      return $res->getBody()->getContents();
    }
    throw new Exception("Content Type not supported for OIDC jwks_uri " . $contentType);
  }

  public function jwk_uri(): array{
    $endpoint = $this->ctrlParamsJwkUri();
    try{
      $res = $this->client->request('GET', $endpoint, $this->params);
    }catch(\GuzzleHttp\Exception\ClientException $e){
      throw new Exception(json_encode([
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ]));
    }catch(\GuzzleHttp\Exception\RequestException $e){
      throw new Exception($e->getMessage());
    }
    $contentType = $res->getHeader('Content-Type')[0];
    if(preg_match('/^application\/json/', $contentType)){
      $ar = json_decode($res->getBody(), true);
      return $ar;
    }
    throw new Exception("Content Type not supported for OIDC jwks_uri " . $contentType);
  }

  public function getTokens(array $params): array{
    $endpoint = $this->ctrlParamsToken();
    $params = array_merge($this->params, $params);
    try{
      $res = $this->client->request('POST', $endpoint, $params);
    }catch(\GuzzleHttp\Exception\ClientException $e){
      throw new Exception(json_encode([
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ]));
    }catch(\GuzzleHttp\Exception\RequestException $e){
      throw new Exception($e->getMessage());
    }
    $contentType = $res->getHeader('Content-Type')[0];
    if(preg_match('/^application\/json/', $contentType)){
      $ar = json_decode($res->getBody(), true);
      return $ar;
    }
    if(preg_match('/^application\/x-www-form-urlencoded/', $contentType)){
      $contents = urldecode($res->getBody()->getContents());
      $ar = [];
      foreach(explode('&', $contents) as $content){
        list($param, $value) = explode("=", $content);
        $ar[$param] = $value;
      }
      if(isset($ar['error']))
        throw new Exception(json_encode($ar));

      return $ar;
    }
    throw new Exception("Content Type not supported for OIDC get tokens " . $contentType);
  }
  private function ctrlParamsIntro(){
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->introspection_endpoint))
      throw new Exception('introspection_endpoint not set');
    return $fi_config->introspection_endpoint;
  }
  private function ctrlParamsRevoc(){
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->revocation_endpoint))
      throw new Exception('revocation_endpoint not set');
    return $fi_config->revocation_endpoint;
  }
  private function ctrlParamsUserInfo(){
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->userinfo_endpoint))
      throw new Exception('userinfo_endpoint not set');
    return $fi_config->userinfo_endpoint;
  }
  private function ctrlParamsJwkUri(){
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->jwks_uri))
      throw new Exception('jwks_uri not set');
    return $fi_config->jwks_uri;
  }
  private function ctrlParamsToken(){
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->token_endpoint))
      throw new Exception('token_endpoint not set');
    return $fi_config->token_endpoint;
  }
  public function ctrlParams(){
    if(is_null($this->session->get('FI_PARAMS'))){
      if(!is_null($this->welcomeUrl)){
        $res = $this->client->request('GET', $this->welcomeUrl, $this->params);
        $this->session->put('FI_PARAMS', Utils::jsonDecode($res->getBody()));
      }else{
        $this->session->put('FI_PARAMS', new \stdClass);
      }

    }
    return $this->session->get('FI_PARAMS');
  }
  public function getAuthorizationEndPoint(){
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->authorization_endpoint))
      throw new Exception('authorization_endpoint not set');
    return $fi_config->authorization_endpoint;
  }
  public function addOtherParam(string $key, mixed $value){
    $this->params[$key] = $value;
  }
  public function verifyTls(bool $verify){
    $this->params['verify'] = $verify;
  }
  public function setCert(string|array $cert){
    $this->params['cert'] = $cert;
  }
  public function setHttpProxy(string $proxy){
    if(!isset($this->params['proxy']))
      $this->params['proxy'] = [];
    $this->params['proxy']['http'] = $proxy;
  }
  public function setHttpsProxy(string $proxy){
    if(!isset($params['proxy']))
      $this->params['proxy'] = [];
    $this->params['proxy']['https'] = $proxy;
  }
  public function setNoProxy(array $proxy){
    if(!isset($params['proxy']))
      $this->params['proxy'] = [];
    $this->params['proxy']['no'] = $proxy;
  }
}
