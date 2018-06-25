<?php

namespace ModulusPHP\Http;

use App\Core\Log;
use ModulusPHP\Touch\View;
use ModulusPHP\Http\Requests\Request;

class CSRF
{
  /**
   * Generate a new CSRF Token.
   * 
   * @return bin2hex
   */
  public static function generate()
  {
    return $_SESSION['application.csrf_token'] = self::genTimeStamp().bin2hex(random_bytes(20));
  }

  /**
   * Verify CSRF Token from POST requests made
   * from web.php.
   * 
   * @param  Request $request
   * @return
   */
  public static function verify(Request $request = null)
  {
    $route = debug_backtrace()[3]['file'];

    if (endsWith($route, 'web.php')) {
      if ($request->method() != Request::POST) return;

      $csrfToken = $request->hasInput('csrf_token') ? $request->input('csrf_token') : $request->header('X-CSRF-Token');

      if (self::verTimeStamp(strtok($csrfToken, '==').'==') == false) {
        self::tokenError(1000);
      }

      $csrfToken = substr($csrfToken, strrpos($csrfToken, '=') + 1);
      $sessionToken = substr($_SESSION['application.csrf_token'], strrpos($_SESSION['application.csrf_token'], '=') + 1);

      if (!hash_equals($sessionToken, $csrfToken)) {
        self::tokenError(400);
      }

      unset($_SESSION['application.csrf_token']);
    }
    else {
      return;
    }
  }

  /**
   * Generate timestamp
   * 
   * @return date()
   */
  public static function genTimeStamp()
  {
    return base64_encode(date('Y-m-d H:i:s'));
  }

  /**
   * Verify timestamp
   * 
   * @param  string  $time
   * @return boolean
   */
  public static function verTimeStamp($time)
  {
    $time = base64_decode($time);
    $expire = config('app.session_token.expire');

    if(strtotime($time) < strtotime("-$expire minutes")) {
      return false;
    }

    return true;
  }

  /**
   * Generate error
   *
   * @param  integer $code
   * @return
   */
  public static function tokenError($code)
  {
    unset($_SESSION['application.csrf_token']);
    View::error($code);
    die();
  }
}