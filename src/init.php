<?php
namespace Svgta\OidcClient;
use Svgta\OidcClient\OidcException as Exception;
use Svgta\OidcLib\OidcUtils;
use Svgta\OidcLib\OidcKeys;
use Svgta\OidcLib\OidcSession;

class init
{
  private $welcomeUrl = null;
  private $client_id = null;
  private $client_secret = null;
  public $request = null;
  private $session = null;

  const SESSION_NAME = "SvgtaOidcClient";

  public static function setLogLevel(int $level){
    OidcUtils::setLogLevel($level);
  }

  public function __construct(?string $welcomeUrl = null, ?string $client_id = null, ?string $client_secret = null)
  {
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['welcomeUrl'=> $welcomeUrl]);
    OidcSession::setSessionName(self::SESSION_NAME);
    $this->session = new OidcSession();
    $this->session->delete('FI_PARAMS');
    $this->welcomeUrl = $welcomeUrl;
    if(!is_null($client_id))
      $this->client_id($client_id);
    if(!is_null($client_secret))
      $this->client_secret($client_secret);
    $this->request = new OidcRequest($welcomeUrl, $this->session);
  }

  public function keysManager(): OidcKeys{
    $this->request->ctrlParams();
    return new OidcKeys();
  }

  public function setSessionKey(string $key){
    OidcSession::setSessionKey($key);
    $this->request->ctrlParams();
  }

  public function logout(?string $id_token = null, ?string $redirect_uri = null): void{
    $res = new OidcLogout($id_token, $redirect_uri, $this->session);
    $res->doLogout();
  }

  public function add_OP_info(string $key, mixed $value): void{
    if(is_array($value) || is_object($value))
      $logValue = json_encode($value);
    else
      $logValue = $value;
    $this->request->ctrlParams();
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['key' => $key, 'value' => $logValue]);
    $fi_params = $this->session->get('FI_PARAMS');
    $fi_params->{$key} = $value;
    $this->session->put('FI_PARAMS', $fi_params);
  }
  public function client_id(string $client_id): void{
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['client_id' => $client_id]);
    $this->client_id = $client_id;
  }
  public function client_secret(string $client_secret): void{
    $this->client_secret = $client_secret;
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['client_secret' => 'not loggable info']);
    $keyLen = mb_strlen($client_secret, '8bit');
    if($keyLen < 32)
      OidcUtils::log(LOG_WARNING, 'The client_secret is to small to verify HS256 signature');
    if($keyLen < 48)
      OidcUtils::log(LOG_WARNING, 'The client_secret is to small to verify HS384 signature');
    if($keyLen < 64)
      OidcUtils::log(LOG_WARNING, 'The client_secret is to small to verify HS512 signature');
    $oidcKeys = new OidcKeys();
    $oidcKeys
      ->use_for_encDec()
      ->set_kid('client_secret')
      ->set_secret_key($this->client_secret)
      ->build();
    $oidcKeys
      ->use_for_signVerify()
      ->set_kid('client_secret')
      ->set_secret_key($this->client_secret)
      ->build();
  }
  public function authorization(string $redirectUri): OidcAuthorization{
    $this->request->ctrlParams();
    $fi_params = $this->session->get('FI_PARAMS');
    $this->session->clear();
    if(!is_null($fi_params))
      $this->session->put('FI_PARAMS', $fi_params);
    return new OidcAuthorization($this->client_id, $this->request, $redirectUri, $this->session);
  }
  public function token(array $request = []): OidcTokens{
    $this->request->ctrlParams();
    $req = OidcUtils::getRequest($request);
    return new OidcTokens($req, $this->client_id, $this->request, $this->client_secret, $this->session);
  }
  public function userInfo(?string $access_token = null, ?string $id_token = null): array{
    $this->request->ctrlParams();
    $res = new OidcUserInfo($this->client_id, $this->request, $access_token, $id_token, $this->session, $this->client_secret);
    return $res->get();
  }
  public function registration(?string $access_token = null){
    $this->request->ctrlParams();
    return new OidcRegistration($this->request, $access_token);
  }
}
