<?php
namespace Svgta;
use Svgta\OidcException as Exception;

class OidcRegistration
{
  private static $metadata_REQUIRED = [
    'redirect_uris',
  ];

  private static $metadata_OPTIONAL = [
    'response_types',
    'grant_types',
    'application_type',
    'contacts',
    'client_name',
    'logo_uri',
    'client_uri',
    'policy_uri',
    'tos_uri',
    'jwks_uri',
    'jwks',
    'sector_identifier_uri',
    'subject_type',
    'id_token_signed_response_alg',
    'id_token_encrypted_response_alg',
    'id_token_encrypted_response_enc',
    'userinfo_signed_response_alg',
    'userinfo_encrypted_response_alg',
    'userinfo_encrypted_response_enc',
    'request_object_signing_alg',
    'request_object_encryption_alg',
    'request_object_encryption_enc',
    'token_endpoint_auth_method',
    'token_endpoint_auth_signing_alg',
    'default_max_age',
    'require_auth_time',
    'default_acr_values',
    'initiate_login_uri',
    'request_uris'
  ];

  private $params = [];
  private $request = null;
  private $access_token = null;

  public function __construct(OidcRequest $request, ?string $access_token = null){
    foreach(self::$metadata_REQUIRED as $p)
      $this->params[$p] = null;
    foreach(self::$metadata_OPTIONAL as $p)
      $this->params[$p] = null;

    $this->request = $request;
    $this->access_token = $access_token;
  }

  // https://www.rfc-editor.org/rfc/rfc7592
  public function delete(string $url): array{
    if(!filter_var($uriUpdate, FILTER_VALIDATE_URL))
      throw new Exception('URL format invalide');
      if(!is_null( $this->access_token))
        $reqParams = [
          'headers' => [
            'Authorization' => 'Bearer ' . $this->access_token,
          ]
        ];
    $ret = $this->request->deleteRegistration($reqParams, $url);
    return $ret;
  }

  // https://www.rfc-editor.org/rfc/rfc7592
  public function update(string $url): array{
    if(!filter_var($uriUpdate, FILTER_VALIDATE_URL))
      throw new Exception('URL format invalide');
    $reqParams = $this->checkParams();
    $ret = $this->request->updateRegistration($reqParams, $url);
    return $ret;
  }

  // https://openid.net/specs/openid-connect-registration-1_0.html
  // https://www.rfc-editor.org/rfc/rfc7591
  public function register(): array{
    $reqParams = $this->checkParams();
    $ret = $this->request->registration($reqParams);
    return $ret;
  }

  private function checkParams(): array{
    $params = [];
    foreach($this->params as $k => $v)
      if(!is_null($v))
        $params[$k] = $v;

    $this->checkRequired($params);
    $reqParams = [
      'headers' => [
        'Accept' => 'application/json',
      ],
      'json' => $params,
    ];
    if(!is_null( $this->access_token))
      $reqParams['headers']['Authorization'] = 'Bearer ' . $this->access_token;
    return $reqParams;
  }

  private function checkType(string $keyParam): string{
    switch($keyParam){
      case 'redirect_uris':
      case 'response_types':
      case 'grant_types':
      case 'contacts':
      case 'jwks':
      case 'request_uris':
        $type = "array";
        break;
      default:
        $type = "string";
    }
    return $type;
  }

  private function checkRequired(array $params){
    foreach(self::$metadata_REQUIRED as $p)
      if(!isset($params[$p]))
        throw new Exception($p . ' is REQUIRED');
  }

  public function set_params(string $key, mixed $value): void{
    $type = $this->checkType($key);
    if($type === "array"){
      if(is_null($this->params[$key]))
        $this->params[$key] = [$value];
      else
        $this->params[$key] = array_merge($this->params[$key], $value);
    }else{
      $this->params[$key] = $value;
    }
  }

  public function get_REQUIRED_PARAMS(): array{
    return self::$metadata_REQUIRED;
  }

  public function get_OPTIONAL_PARAMS(): array{
    return self::$metadata_OPTIONAL;
  }

}
