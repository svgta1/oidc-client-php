<?php
namespace Svgta;
use Svgta\OidcException as Exception;

class OidcClient
{
  private $welcomeUrl = null;
  private $client_id = null;
  private $client_secret = null;
  public $request = null;
  private $session = null;

  public static function setLogLevel(int $level){
    OidcUtils::setLogLevel($level);
  }

  public function __construct($welcomeUrl = null, $client_id = null, $client_secret = null)
  {
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['welcomeUrl'=> $welcomeUrl]);
    $this->session = new OidcSession();
    $this->welcomeUrl = $welcomeUrl;
    if(!is_null($client_id))
      $this->client_id($client_id);
    if(!is_null($client_secret))
      $this->client_secret($client_secret);
    $this->request = new OidcRequest($welcomeUrl);
    $this->request->ctrlParams();
  }

  public function logout($id_token = null, $redirect_uri = null){
    $res = new OidcLogout($id_token, $redirect_uri);
    $res->doLogout();
  }

  public function add_OP_info(string $key, mixed $value){
    if(is_array($value) || is_object($value))
      $logValue = json_encode($value);
    else
      $logValue = $value;
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['key' => $key, 'value' => $logValue]);
    $fi_params = $this->session->get('FI_PARAMS');
    $fi_params->{$key} = $value;
    $this->session->put('FI_PARAMS', $fi_params);
  }
  public function client_id(string $client_id){
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['client_id' => $client_id]);
    $this->client_id = $client_id;
  }
  public function client_secret(string $client_secret){
    OidcUtils::setDebug(__CLASS__, __FUNCTION__, ['client_secret' => 'not loggable info']);
    $this->client_secret = $client_secret;
  }
  public function authorization(string $redirectUri): OidcAuthorization{
    $this->session->clear();
    return new OidcAuthorization($this->client_id, $this->request, $redirectUri);
  }
  public function token(array $request = []): OidcTokens{
    $req = OidcUtils::getRequest($request);
    return new OidcTokens($req, $this->client_id, $this->request, $this->client_secret);
  }
  public function userInfo($access_token = null, $id_token = null): array{
    $res = new OidcUserInfo($this->client_id, $this->request, $access_token, $id_token);
    return $res->get();
  }

}
