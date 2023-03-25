<?php
namespace Svgta;
use Svgta\OidcException as Exception;

class OidcSession
{
  private static $name = "SvgtaOidcClient";
  public function __construct(){
    if(session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if(!isset($_SESSION['SvgtaOidcClient']))
      $_SESSION[self::$name] = [];
  }

  public function put(string $key, mixed $value): void{
    $_SESSION[self::$name][$key] = $value;
  }

  public function get(string $key): mixed{
    if(isset($_SESSION[self::$name][$key]))
      return $_SESSION[self::$name][$key];
    return null;
  }

  public function delete(string $key): void{
    if(isset($_SESSION[self::$name][$key]))
      unset($_SESSION[self::$name][$key]);
  }

  public function clear(): void{
    $_SESSION[self::$name] = [];
  }
}
