<?php
namespace Svgta\OidcClient;
use Svgta\OidcClient\OidcException as Exception;
use Svgta\Lib\Session;
use Svgta\Lib\Utils;

class OidcLogout
{
  private $id_token = null;
  private $redirect_uri = null;
  private $session = null;

  public function __construct(?string $id_token = null, ?string $redirect_uri = null, Session $session){
    $this->session = $session;
    if(!is_null($id_token))
      if(!is_string($id_token))
        throw new Exception('id_token must be a string');
    if(!is_null($redirect_uri))
      if(!is_string($redirect_uri))
        throw new Exception('redirect_uri must be a string');
    if(is_null($id_token)){
      $tokens = $this->session->get('tokens');
      if(isset($tokens['id_token']))
        $id_token = $tokens['id_token'];
    }
    $this->id_token = $id_token;
    $this->redirect_uri = $redirect_uri;
  }
  public function doLogout(): void{
    Utils::setDebug(__CLASS__, __FUNCTION__);
    $logoutUri = $this->getLogoutUri();
    $this->session->clear();
    header('Location: ' . $logoutUri);
  }
  public function getLogoutUri(): string{
    $fi_params = $this->session->get('FI_PARAMS');
    if(!isset($fi_params->end_session_endpoint))
      throw new Exception('end_session_endpoint not set');
    $param = [];
    if(!is_null($this->id_token))
      $param['id_token_hint'] = $this->id_token;
    if(!is_null($this->redirect_uri))
      $param['post_logout_redirect_uri'] = $this->redirect_uri;
    Utils::setDebug(__CLASS__, __FUNCTION__, [
      'id_token' => $this->id_token,
      'redirect_uri' => $this->redirect_uri
    ]);
    $logoutUri = $fi_params->end_session_endpoint;
    if(count($param) > 0)
      $logoutUri .= '?' . http_build_query($param);
    return $logoutUri;
  }
}
