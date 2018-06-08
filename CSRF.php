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
    return $_SESSION['application.csrf_token'] = bin2hex(random_bytes(32));
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

      if (!hash_equals($_SESSION['application.csrf_token'], $csrfToken)) {
        unset($_SESSION['application.csrf_token']);
        View::error(400);
        die();
      }

      unset($_SESSION['application.csrf_token']);
    }
    else {
      return;
    }
  }
}