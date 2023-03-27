<?php
namespace Svgta;
use Svgta\OidcException as Exception;

class OidcTokens
{
  use OidcJWTVerifyTrait;
  private static $default_auth_method_order = [
    'private_key_jwt',
    'client_secret_jwt',
    'client_secret_basic',
    'client_secret_post',
    'none',
  ];

  private $client_id = null;
  private $client_secret = null;
  private $session = null;
  private $authParams = null;
  private $request = null;
  private $HttpRequest = null;
  private $code = null;
  private $tokens = null;
  private $auth_method = null;
  private $privateKey = null;
  private $sigAlg = null;

  public function __construct(array $HttpRequest, string $client_id, OidcRequest $request, ?string $client_secret = null, OidcSession $session){
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    if(isset($HttpRequest['error'])){
      $desc = isset($HttpRequest['error_description']) ? $HttpRequest['error_description'] : $HttpRequest['error'];
      throw new Exception($desc);
    }
    $this->HttpRequest = $HttpRequest;
    $this->request = $request;
    $this->client_id = $client_id;
    $this->client_secret = $client_secret;
    $this->session = $session;
    $this->authParams = $this->session->get('authParams');
  }

  public function introspect_token(string $token, ?string $type = null): array{
    if(!is_null($type)){
      if(!is_string($type))
        throw new Exception('The type of token must be as string');
      if($type !== 'refresh_token' && $type !== 'access_token')
        throw new Exception('The type of token must be refresh_token or access_token');
    }
    $authParams = $this->getAuthParams($this->auth_method);
    $authParams['form_params']['token'] = $token;
    if(!is_null($type))
      $authParams['form_params']['token_type_hint'] = $type;

    $tokens = $this->session->get('tokens');
    if(is_null($type)){
      if(isset($tokens['access_token']) && $tokens['access_token'] == $token){
        $authParams['form_params']['token_type_hint'] = 'access_token';
      }
      if(isset($tokens['refresh_token']) && $tokens['refresh_token'] == $token){
        $authParams['form_params']['token_type_hint'] = 'refresh_token';
      }
    }
    $response = $this->request->introspect_token($authParams);
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['response'=> $response]);
    return $response;
  }

  public function getTokensFromSession(){
    return $this->session->get('tokens');
  }

  public function revoke_token(string $token, ?string $type = null): array{
    if(!is_null($type)){
      if(!is_string($type))
        throw new Exception('The type of token must be as string');
      if($type !== 'refresh_token' && $type !== 'access_token')
        throw new Exception('The type of token must be refresh_token or access_token');
    }
    $authParams = $this->getAuthParams($this->auth_method);
    $authParams['form_params']['token'] = $token;
    if(!is_null($type))
      $authParams['form_params']['token_type_hint'] = $type;

    $tokens = $this->session->get('tokens');
    if(!is_null($type)){
      if(isset($tokens[$type]))
        unset($tokens[$type]);
    }else{
      if(isset($tokens['access_token']) && $tokens['access_token'] == $token){
        unset($tokens['access_token']);
        $authParams['form_params']['token_type_hint'] = 'access_token';
      }
      if(isset($tokens['refresh_token']) && $tokens['refresh_token'] == $token){
        unset($tokens['refresh_token']);
        $authParams['form_params']['token_type_hint'] = 'refresh_token';
      }
    }
    $response = $this->request->revocation_endpoint($authParams);
    $this->session->put('tokens', $tokens);
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['response'=> $response]);
    return $response;
  }

  public function refresh_token(?string $refresh_token = null): array{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['refresh_token'=> $refresh_token]);
    $grant_type = 'refresh_token';
    $this->test_grant_type($grant_type);
    if(is_null($refresh_token)){
      $tokens = $this->session->get('tokens');
      if(!isset($tokens['refresh_token']))
        throw new Exception('No refresh_token set');
      $refresh_token = $tokens['refresh_token'];
    }

    if(is_null($this->auth_method))
      $this->getAuthMethod();
    $authParams = $this->getAuthParams($this->auth_method);
    $authParams['form_params']['grant_type'] = $grant_type;
    $authParams['form_params']['refresh_token'] = $refresh_token;
    if(isset($this->authParams['nonce']))
      unset($this->authParams['nonce']);

    return $this->getTokens($authParams);
  }

  public function client_credentials(string $scopes = ''): array{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['scopes'=> $scopes]);
    OidcUtils::log(LOG_INFO, 'client_credentials flow is used when applications request an access_token to access their own resources.');
    $grant_type = 'client_credentials';
    $this->test_grant_type($grant_type);
    if(is_null($this->auth_method))
      $this->getAuthMethod();
    $authParams = $this->getAuthParams($this->auth_method);
    $authParams['form_params']['grant_type'] = $grant_type;
    if(strlen($scopes) > 1)
      $authParams['form_params']['scope'] = $scopes;
    return $this->getTokens($authParams);
  }

  public function password_grant(string $username, string $password): array{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['username'=> $username, 'password' => 'not loggable info']);
    OidcUtils::log(LOG_WARNING, 'The Password grant flow should not be used. See explaination on : https://www.oauth.com/oauth2-servers/access-tokens/password-grant/');
    $grant_type = 'password';
    $this->test_grant_type($grant_type);
    if(is_null($this->auth_method))
      $this->getAuthMethod();
    $authParams = $this->getAuthParams($this->auth_method);
    $authParams['form_params']['grant_type'] = $grant_type;
    $authParams['form_params']['username'] = $username;
    $authParams['form_params']['password'] = $password;
    return $this->getTokens($authParams);
  }

  private function test_grant_type(string $grant_type): void{
    $fi_config = $this->session->get('FI_PARAMS');
    if(is_null($fi_config))
      throw new Exception('OP well-known conf not set');
    if(!isset($fi_config->grant_types_supported))
      $fi_config->grant_types_supported = ['authorization_code'];
    if(isset($fi_config->grant_types_supported) && !in_array($grant_type, $fi_config->grant_types_supported))
      throw new Exception('Grant type ' . $grant_type . ' not supported by the OP');
  }

  private function getAuthParams(string $auth_method): array{
    $params = [];
    $params['form_params'] = [];
    if($auth_method == 'pkce'){
      $params['form_params']['code_verifier'] = $this->session->get('code_verifier');
      if(is_null($this->client_secret)){
        $params['form_params']['client_id'] = $this->client_id;
        OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['params'=> $params]);
        return $params;
      }
      $this->getAuthMethod(false);
      $auth_method = $this->auth_method;
    }
    switch($auth_method){
      case 'client_secret_basic':
        $params['auth'] = [$this->client_id, $this->client_secret];
        break;
      case 'client_secret_post':
        $params['form_params']['client_id'] = $this->client_id;
        $params['form_params']['client_secret'] = $this->client_secret;
        break;
      case 'client_secret_jwt':
        if(is_null($this->client_secret))
          throw new Exception('The client_secret is not set');
        $jwt = OidcJWT::gen_client_secret_jwt($this->client_secret, $this->client_id, $this->session->get('FI_PARAMS')->token_endpoint);
        if(!is_null($this->sigAlg))
          $jwt->setAlg($this->sigAlg);
        $params['form_params']['client_id'] = $this->client_id;
        $params['form_params']['client_assertion_type'] = "urn:ietf:params:oauth:client-assertion-type:jwt-bearer";
        $params['form_params']['client_assertion'] = $jwt->signPayload();
        break;
      case 'private_key_jwt':
        if(is_null($this->privateKey))
          throw new Exception('The privateKey is not set for private_key_jwt authentication');
        $jwt = OidcJWT::gen_private_key_jwt($this->privateKey, $this->client_id, $this->session->get('FI_PARAMS')->token_endpoint);
        if(!is_null($this->sigAlg))
          $jwt->setAlg($this->sigAlg);
        $params['form_params']['client_id'] = $this->client_id;
        $params['form_params']['client_assertion_type'] = "urn:ietf:params:oauth:client-assertion-type:jwt-bearer";
        $params['form_params']['client_assertion'] = $jwt->signPayload();
        break;
    }
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['params'=> $params]);
    return $params;
  }

  public function get_tokens(): array{
    $this->getAuthMethod();
    $this->session->delete('tokens');
    $flowType = $this->session->get('flowType');
    switch($flowType){
      case 'code':
        $this->flowCode();
        break;
      case 'implicit':
        $this->flowImplicit();
        break;
      case 'hybrid':
        $this->flowHybrid();
        break;
    }
    $grant_type = 'authorization_code';
    $this->test_grant_type($grant_type);
    $flowType = $this->session->get('flowType');
    if($flowType == 'implicit'){
      if(is_null($this->session->get('tokens')))
        throw new Exception('Not allowed for ' . $flowType . ' flow');
      return $this->session->get('tokens');
    }
    if(is_null($this->code))
      throw new Exception('Code not set');
    if(is_null($this->auth_method))
      $this->getAuthMethod();
    $authParams = $this->getAuthParams($this->auth_method);
    $authParams['form_params']['code'] = $this->code;
    $authParams['form_params']['grant_type'] = $grant_type;
    $authParams['form_params']['redirect_uri'] = $this->session->get('authParams')['redirect_uri'];
    return $this->getTokens($authParams);
  }

  private function getTokens(array $params): array{
    $sesTokens = $this->session->get('tokens');
    $tokens = $this->request->getTokens($params);
    if(!is_null($sesTokens)){
      //avoid to delete refresh_token if not given back;
      $tokens = array_merge($sesTokens, $tokens);
    }
    if(!isset($tokens['token_type']) || (strtolower($tokens['token_type']) != "bearer"))
      throw new Exception('Token type bearer expected');
    if(isset($tokens['id_token'])){
      $access_token = isset($tokens['access_token']) ? $tokens['access_token'] : null;
      $this->ctrlIdToken($tokens['id_token'], $access_token);
    }
    $this->session->put('tokens', $tokens);
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['tokens'=> $tokens]);
    return $tokens;
  }

  private function ctrlIdToken(string $id_token, ?string $access_token): void{
    if(!is_null($access_token) && !is_string($access_token))
      throw new Exception('Bad type for access_token');

    $parse = OidcJWT::parseJWS($id_token);
    $payload = $parse['payload'];
    if(!isset($payload['sub']))
      throw new Exception('sub claim is required in id_token');
    $alg = $parse['header']['alg'];
    if(isset($parse['header']['enc']))
      throw new Exception('Encrypted id_token not supported');

    $this->ctrlJWT_sign($parse['ressource'], $alg);
    $this->ctrlJWT_at_hash($payload, $access_token, $alg);
    $this->ctrlJWT_c_hash($payload, $alg);
    $this->ctrlJWT_time($payload);
    $this->ctrlJWT_nonce($payload);
    $this->ctrlJWT_iss($payload);
    $this->ctrlJWT_aud($payload);
  }

  private function getAuthMethod(bool $pkce = true): void{
    if($pkce && !is_null($this->session->get('code_verifier'))){
      $this->auth_method = 'pkce';
    }else{
      $fi_config = $this->session->get('FI_PARAMS');
      if(!isset($fi_config->token_endpoint_auth_methods_supported))
        $fi_config->token_endpoint_auth_methods_supported = ['client_secret_basic'];
      foreach(self::$default_auth_method_order as $method){
        if(($method == "private_key_jwt") && is_null($this->privateKey))
          continue;
        if(!in_array($method, $fi_config->token_endpoint_auth_methods_supported))
          continue;
        $keyLen = mb_strlen($this->client_secret, '8bit');
        if(($method == "client_secret_jwt") && ($keyLen < 32))
          continue;
        $this->auth_method = $method;
        break;
      }
    }
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['auth_method' => $this->auth_method]);
  }

  public function setSigAlg(string $alg): void{
    $this->sigAlg = $alg;
  }

  public function setPrivateKeyFile(string $privateKeyPath, ?string $password = null): void{
    $privateKey = is_file($privateKeyPath);
    if(!$privateKey)
      throw new Exception('The private key is not accessible');
    $this->setPrivateKey(file_get_contents($privateKeyPath), $password);
  }

  public function setPrivateKey(string $pem, string $password = null): void{
    $this->privateKey = [
      'pem' => $pem,
      'pwd' => $password
    ];
  }
  public function setPrivateKeyKid(string $kid): void{
    $this->privateKey['kid'] = $kid;
  }
  public function setPrivateKeyX5t(string $x5t): void{
    $this->privateKey['x5t'] = $x5t;
  }

  public function set_auth_method(string $method): void{
    if(!is_null($this->session->get('code_verifier'))){
      if($method !== 'pkce')
        throw new Exception('The auth_method must be set to "pkce"');
      $this->auth_method = 'pkce';
    }else{
      $fi_config = $this->session->get('FI_PARAMS');
      if(!isset($fi_config->token_endpoint_auth_methods_supported))
        $fi_config->token_endpoint_auth_methods_supported = ['client_secret_basic'];
      $auth_method_supported = $fi_config->token_endpoint_auth_methods_supported;
      if(!in_array($method, $auth_method_supported))
        throw new Exception('Auth method not supported by the OP');
      if(!in_array($method, self::$default_auth_method_order))
        throw new Exception('Auth method not supported');
      if($method == 'private_key_jwt'){
        if(is_null($this->privateKey))
          throw new Exception('The private key is not set. Use setPrivateKey(string $privateKeyPath) method befor this one.');
      }
      $this->auth_method = $method;
    }
  }

  private function _ctrlState(): void{
    if(isset($this->authParams['state']) && !isset($this->HttpRequest['state']))
      throw new Exception('Bad callback state return');
    if(isset($this->HttpRequest['state']) && ($this->HttpRequest['state'] !== $this->authParams['state']))
      throw new Exception('Bad callback state value');
    unset($this->authParams['state']);
    $this->session->put('authParams', $this->authParams);
  }
  private function flowCode(): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    if(!isset($this->HttpRequest['code']))
      throw new Exception('Bad callback code return');
    $this->_ctrlState();
    $this->code = $this->HttpRequest['code'];
  }
  private function flowImplicit(): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    $this->_ctrlState();
    if(!isset($this->HttpRequest['id_token']))
      throw new Exception('Bad callback return. id_token is required');
    $tokens = [
      'access_token' => isset($this->HttpRequest['access_token']) ? $this->HttpRequest['access_token'] : null,
      'id_token' => $this->HttpRequest['id_token'],
      'token_type' => isset($this->HttpRequest['token_type']) ? $this->HttpRequest['token_type'] : null,
      'expires_in' => isset($this->HttpRequest['token_type']) ? $this->HttpRequest['token_type'] : 0,
    ];
    $response_type = explode(' ', $this->authParams['response_type']);
    if(in_array('token', $response_type)){
      if(is_null($tokens['access_token']))
        throw new Exception('The OP must give the access_token');
      if(is_null($tokens['token_type']))
        throw new Exception('The OP must give the token_type with the access_token');
    }
    $this->ctrlIdToken($tokens['id_token'], $tokens['access_token']);
    $this->session->put('tokens', $tokens);
  }
  private function flowHybrid(): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__);
    if(isset($this->HttpRequest['id_token']))
      $this->flowImplicit();
    $this->session->delete('tokens');
    $this->flowCode();
  }
}
