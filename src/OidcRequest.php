<?php
namespace Svgta\OidcClient;
use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use Psr\Http\Message\ResponseInterface;
use Svgta\OidcClient\OidcException as Exception;

class OidcRequest
{
  private $params = [];
  private $client = null;
  private $welcomeUrl = null;
  public static $FI_config = null;
  private $session = null;

  public function __construct(?string $welcomeUrl = null, OidcSession $session){
    $this->welcomeUrl = $welcomeUrl;
    $this->client = new Client();
    $this->session = $session;
  }

  public function deleteRegistration(array $params, string $url): array{
    $params = array_merge($this->params, $params);
    $res = $this->doRequest('DELETE', $url, $params);
    $ret = $this->contentType_json($res);
    if(is_null($ret))
      throw new Exception("Content Type not supported for OIDC dynamic registration deletion" . $res->getHeader('Content-Type')[0]);

    if(isset($ar['error']))
      throw new Exception(json_encode($ar));

    return $ret;
  }

  public function updateRegistration(array $params, string $url): array{
    $params = array_merge($this->params, $params);
    $res = $this->doRequest('PUT', $url, $params);
    $ret = $this->contentType_json($res);
    if(is_null($ret))
      throw new Exception("Content Type not supported for OIDC dynamic registration updating" . $res->getHeader('Content-Type')[0]);

    if(isset($ar['error']))
      throw new Exception(json_encode($ar));

    return $ret;
  }

  public function registration(array $params): array{
    $endpoint = $this->ctrlParamsRegistration();
    $params = array_merge($this->params, $params);
    $res = $this->doRequest('POST', $endpoint, $params);
    $ret = $this->contentType_json($res);
    if(is_null($ret))
      throw new Exception("Content Type not supported for OIDC dynamic registration " . $res->getHeader('Content-Type')[0]);

    if(isset($ar['error']))
      throw new Exception(json_encode($ar));

    return $ret;
  }

  public function introspect_token(array $params): array{
    $endpoint = $this->ctrlParamsIntro();
    $params = array_merge($this->params, $params);
    $res = $this->doRequest('POST', $endpoint, $params);
    $ret = $this->contentType_json($res);
    if(is_null($ret))
      $ret = $this->contentType_form($res);
    if(is_null($ret))
      throw new Exception("Content Type not supported for OIDC instrospection " . $res->getHeader('Content-Type')[0]);

    if(isset($ar['error']))
      throw new Exception(json_encode($ar));

    return $ret;
  }
  public function revocation_endpoint(array $params): array{
    $endpoint = $this->ctrlParamsRevoc();
    $params = array_merge($this->params, $params);
    $res = $this->doRequest('POST', $endpoint, $params);
    $ret = $this->contentType_json($res);
    if(is_null($ret))
      $ret = $this->contentType_form($res);
    if(is_null($ret))
      throw new Exception("Content Type not supported for OIDC revocation " . $res->getHeader('Content-Type')[0]);

    if(isset($ar['error']))
      throw new Exception(json_encode($ar));

    return $ret;
  }

  public function userInfo(string $access_token){
    $params = ['headers' => ['Authorization' => 'Bearer ' . $access_token]];
    $params = array_merge($this->params, $params);
    $endpoint = $this->ctrlParamsUserInfo();
    $res = $this->doRequest('GET', $endpoint, $params);
    $ret = $this->contentType_json($res);
    if(is_null($ret))
      $ret = $this->contentType_jwt($res);

    if(is_null($ret))
      throw new Exception("Content Type not supported for OIDC jwks_uri " . $res->getHeader('Content-Type')[0]);

    return $ret;
  }

  public function jwk_uri(): array{
    $endpoint = $this->ctrlParamsJwkUri();
    $res = $this->doRequest('GET', $endpoint, $this->params);
    $ret = $this->contentType_json($res);
    if(is_null($ret))
      throw new Exception("Content Type not supported for OIDC jwks_uri " . $res->getHeader('Content-Type')[0]);

    return $ret;
  }

  public function getTokens(array $params): array{
    $endpoint = $this->ctrlParamsToken();
    $params = array_merge($this->params, $params);
    $res = $this->doRequest('POST', $endpoint, $params);

    $ret = $this->contentType_json($res);
    if(is_null($ret))
      $ret = $this->contentType_form($res);
    if(is_null($ret))
      throw new Exception("Content Type not supported for OIDC get tokens " . $res->getHeader('Content-Type')[0]);

    if(isset($ar['error']))
      throw new Exception(json_encode($ar));

    return $ret;
  }

  private function contentType_jwt(ResponseInterface $res): ?array{
    $contentType = $res->getHeader('Content-Type')[0];
    return $res->getBody()->getContents();
  }

  private function contentType_form(ResponseInterface $res): ?array{
    $contentType = $res->getHeader('Content-Type')[0];
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

    return null;
  }

  private function contentType_json(ResponseInterface $res): ?array{
    $contentType = $res->getHeader('Content-Type')[0];
    if(preg_match('/^application\/json/', $contentType)){
      $ar = json_decode($res->getBody(), true);
      return $ar;
    }
    return null;
  }

  private function doRequest(string $method, string $uri, array $params): ResponseInterface{
    try{
      $res = $this->client->request($method, $uri, $params);
    }catch(\GuzzleHttp\Exception\ClientException $e){
      throw new Exception(json_encode([
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
        'body' => $e->getResponse()->getBody()->getContents(),
      ]));
    }catch(\GuzzleHttp\Exception\RequestException $e){
      throw new Exception($e->getMessage());
    }
    return $res;
  }
  private function ctrlParamsRegistration(): string{
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->registration_endpoint))
      throw new Exception('registration_endpoint not set');
    return $fi_config->registration_endpoint;
  }
  private function ctrlParamsIntro(): string{
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->introspection_endpoint))
      throw new Exception('introspection_endpoint not set');
    return $fi_config->introspection_endpoint;
  }
  private function ctrlParamsRevoc(): string{
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->revocation_endpoint))
      throw new Exception('revocation_endpoint not set');
    return $fi_config->revocation_endpoint;
  }
  private function ctrlParamsUserInfo(): string{
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->userinfo_endpoint))
      throw new Exception('userinfo_endpoint not set');
    return $fi_config->userinfo_endpoint;
  }
  private function ctrlParamsJwkUri(): string{
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->jwks_uri))
      throw new Exception('jwks_uri not set');
    return $fi_config->jwks_uri;
  }
  private function ctrlParamsToken(): string{
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->token_endpoint))
      throw new Exception('token_endpoint not set');
    return $fi_config->token_endpoint;
  }
  public function ctrlParams(): object{
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
  public function getAuthorizationEndPoint(): string{
    $fi_config = $this->ctrlParams();
    if(!isset($fi_config->authorization_endpoint))
      throw new Exception('authorization_endpoint not set');
    return $fi_config->authorization_endpoint;
  }
  public function addOtherParam(string $key, mixed $value): void{
    $this->params[$key] = $value;
  }
  public function verifyTls(bool $verify): void{
    $this->params['verify'] = $verify;
  }
  public function setCert(string|array $cert): void{
    $this->params['cert'] = $cert;
  }
  public function setHttpProxy(string $proxy): void{
    if(!isset($this->params['proxy']))
      $this->params['proxy'] = [];
    $this->params['proxy']['http'] = $proxy;
  }
  public function setHttpsProxy(string $proxy): void{
    if(!isset($params['proxy']))
      $this->params['proxy'] = [];
    $this->params['proxy']['https'] = $proxy;
  }
  public function setNoProxy(array $proxy): void{
    if(!isset($params['proxy']))
      $this->params['proxy'] = [];
    $this->params['proxy']['no'] = $proxy;
  }
}
