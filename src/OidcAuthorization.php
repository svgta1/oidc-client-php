<?php
namespace Svgta;
use Svgta\OidcException as Exception;

class OidcAuthorization
{
  private static $defaultScopes = ['openid'];
  private static $defaultresponse_type = 'code';
  private static $flowTypeStatic = [
    'implicit' => [
      'min_length' => 1,
      'max_length' => 2,
      'id_token' => 'MUST',
      'token' => 'MUST',
    ],
    'hybrid' => [
      'min_length' => 2,
      'max_length' => 3,
      'code' => 'MUST',
      'id_token' => 'MAY',
      'token' => 'MAY',
    ],
    'code' => [
      'min_length' => 1,
      'max_length' => 1,
      'code' => 'MUST',
    ],
    'none' => [
      'min_length' => 1,
      'max_length' => 1,
      'none' => 'MUST',
    ],
  ];

  private static $display_values = [
    'page',
    'popup',
    'touch',
    'wap',
  ];

  private static $prompt_values = [
    'none',
    'login',
    'consent',
    'select_account',
    'create',
  ];

  private static $code_challenge_methods = [
    "plain",
    "S256",
  ];

  private $client_id = null;
  private $redirect_uri = null;
  private $scopes = [];
  private $endpoint = null;
  private $response_type = null;
  private $access_type = null;
  private $flowType = null;
  private $session = null;
  private $response_mode = null;
  private $display = null;
  private $prompt = null;
  private $max_age = null;
  private $ui_locales = null;
  private $id_token_hint = null;
  private $login_hint = null;
  private $acr_values = null;
  private $code_challenge = null;
  private $code_challenge_method = null;
  private $state = null;
  private $nonce = null;

  public function __construct(string $client_id, OidcRequest $request, string $redirectUri, OidcSession $session){
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, [
      'client_id'=> $client_id,
      'redirectUri' => $redirectUri,
    ]);
    $this->client_id = $client_id;
    $this->scopes = self::$defaultScopes;
    $this->redirect_uri = $redirectUri;
    $this->endpoint = $request->getAuthorizationEndPoint();
    $this->session = $session;
  }

  public function exec(): void{
    $uri = $this->getUri();
    header('Location: ' . $uri);
  }

  public function getUri(): string{
    if(is_null($this->response_type) || is_null($this->flowType))
      $this->set_response_type(self::$defaultresponse_type);
    if(!is_null($this->code_challenge) && ($this->flowType !== 'code'))
      throw new Exception('PKCE must be used with flow "code"');
    $params = $this->lightUriParams($this->uriParams());
    $uri = $this->endpoint . '?' . http_build_query($params);
    $this->session->put('flowType', $this->flowType);
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, [
      'uri' => $uri,
      'params' => $params,
    ]);
    return $uri;
  }
  private function lightUriParams($params): array{
    $ar = [];
    foreach($params as $k => $v){
      if(is_null($v))
        continue;
      $ar[$k] = $v;
    }
    return $ar;
  }

  private function uriParams(): array{
    $params = [
      'scope' => $this->getScopes(),
      'response_type' => $this->response_type,
      'client_id' => $this->client_id,
      'redirect_uri' => $this->redirect_uri,
      'state' => $this->state,
      'response_mode' => $this->response_mode,
      'nonce' => $this->nonce,
      'display' => $this->display,
      'prompt' => $this->prompt,
      'max_age' => $this->max_age,
      'ui_locales' => $this->ui_locales,
      'id_token_hint' => $this->id_token_hint,
      'login_hint' => $this->login_hint,
      'acr_values' => $this->acr_values,
      'code_challenge' => $this->code_challenge,
      'code_challenge_method' => $this->code_challenge_method,
      'access_type' => $this->access_type,
    ];
    if(is_null($params['scope']))
      throw new Exception('Scope not defined');
    if(is_null($params['response_type']))
      throw new Exception('response_type not defined');
    if(is_null($params['client_id']))
      throw new Exception('client_id not defined');
    if(is_null($params['redirect_uri']))
      throw new Exception('redirect_uri not defined');
    if(($this->flowType == 'implicit') && is_null($params['nonce']))
      throw new Exception('nonce not defined');
    $this->session->put('authParams', $params);
    return $params;
  }
  public function set_state(): void{
    $this->state = OidcUtils::randomString();
  }
  public function set_nonce(): void{
    $this->nonce = OidcUtils::randomString();
  }
  public function set_code_challenge_method(string $method): void{
    $fi_config = $this->session->get('FI_PARAMS');
    if(!isset($fi_config->code_challenge_methods_supported))
      throw new Exception('OP does not accept PKCE flwo');
    $methodSupported = $fi_config->code_challenge_methods_supported;
    if(!in_array($method, $methodSupported))
      throw new Exception('Code challenge method not supported by the OP');
    if(!in_array($method, self::$code_challenge_methods))
      throw new Exception('Code challenge method not supported');
    $code = OidcUtils::randomString();
    $this->session->put('code_verifier', $code);
    switch($method){
      case 'plain':
        $code_challenge = $code;
        break;
      case 'S256':
        $code_challenge = OidcUtils::base64url_encode(hash('sha256', $code, true));
        break;
    }
    $this->code_challenge = $code_challenge;
    $this->code_challenge_method = $method;
  }
  public function set_acr_values(string $acr_values): void{
    $this->acr_values = $acr_values;
  }
  public function set_login_hint(string $login_hint): void{
    $this->login_hint = $login_hint;
  }
  public function set_id_token_hint(string $id_token_hint): void{
    $this->id_token_hint = $id_token_hint;
  }
  public function set_ui_locales(string $ui_locales): void{
    $this->ui_locales = $ui_locales;
  }
  public function set_max_age(int $max_age): void{
    $this->max_age = $max_age;
  }
  public function set_prompt(string $prompt): void{
    if(!in_array($prompt, self::$prompt_values))
      throw new Exception('Prompt value not supported');
    $this->prompt = $prompt;
  }
  public function set_display(string $display): void{
    if(!in_array($display, self::$display_values))
      throw new Exception('Display value not supported');
    $this->display = $display;
  }
  public function set_response_mode(string $response_mode): void{
    $this->response_mode = $response_mode;
  }

  public function set_access_type(string $access_type){
    $this->access_type = $access_type;
  }

  public function set_response_type(string $response_type): void{
    $res = explode(' ', $response_type);
    $count = count($res);
    $type = [];
    foreach(self::$flowTypeStatic as $flowtype => $ar){
      if($count >= $ar['min_length'] && $count <= $ar['max_length'])
        $type[$flowtype] = $ar;
    }
    if(count($type) == 0)
      throw new Exception('No flow type found');
    foreach($type as $flowtype => $ar){
      $flow = null;
      foreach($res as $req){
        if(!isset($ar[$req])){
          $flow = null;
          continue 2;
        }
        if($ar[$req] == 'MUST')
          $flow = $flowtype;
      }
      if(is_null($flow)){
        continue;
      }else{
        break;
      }
    }
    if(is_null($flow))
      throw new Exception('No flow type found');

    $fi_config = $this->session->get('FI_PARAMS');
    $fiResSupported = $fi_config->response_types_supported;
    $fiResFound = false;
    foreach($fiResSupported as $fiRes){
      $type = explode(' ', $fiRes);
      if(count($type) !== $count)
        continue;
      foreach($type as $t){
        if(!in_array($t, $res))
          continue 2;
      }
      $fiResFound = true;
    }
    if(!$fiResFound)
      throw new Exception('Response type not supported by the OP' . json_encode($res));

    $this->flowType = $flow;
    $this->response_type = $response_type;
  }

  public function addScope(string $scope): void{
    $scopes = explode(' ', $scope);
    foreach($scopes as $s)
      if(!in_array($s, $this->scopes))
        $this->scopes[] = $s;
  }

  public function getScopes(): string{
    return implode(' ', $this->scopes);
  }

}
