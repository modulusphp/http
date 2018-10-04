<?php

namespace Modulus\Http\Middleware;

use Modulus\Framework\Exceptions\TokenMismatchException;

class VerifyCsrfToken
{
  /**
   * The URIs that should be excluded from CSRF verification.
   *
   * @var array
   */
  protected $except = [];

  /**
   * The URIs that should be excluded from expiration verification.
   *
   * @var array
   */
  protected $canExpire = [
    '/logout'
  ];

  /**
   * $hasExpired
   *
   * @var boolean
   */
  protected $hasExpired = false;

  /**
   * Handle middleware
   *
   * @param  Request $request
   * @return bool
   */
  public function handle($request, $continue) : bool
  {
    if (
      $this->isReading($request) ||
      $this->shouldIgnore($request) ||
      (
        $this->tokenMatches($request) &&
        $this->hasNotExpired($request)
      )
    ) {
      return $continue;
    }

    throw new TokenMismatchException(
      ($this->hasExpired) ? 'Session has expired' : 'Token mismatch'
    );
  }

  /**
   * Determine if the HTTP request uses a ‘read’ verb.
   *
   * @param  \Modulus\Http\Request  $request
   * @return bool
   */
  protected function isReading($request) : bool
  {
    return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
  }

  /**
   * shouldIgnore
   *
   * @param mixed $request
   * @return void
   */
  protected function shouldIgnore($request) : bool
  {
    if (in_array($request->path(), $this->except)) return true;

    return false;
  }

  /**
   * tokenMatches
   *
   * @param mixed $request
   * @return void
   */
  protected function tokenMatches($request) : bool
  {
    if (!isset( $_SESSION['_saini']) || !$_SESSION['_saini']) return false;

    $csrfToken = $request->has('csrf_token') ? $request->input('csrf_token') : ($request->hasHeader('X-CSRF-Token') ? $request->header('X-CSRF-Token') : null);
    $sessionToken =  $_SESSION['_saini'];

    if (!hash_equals($sessionToken, $csrfToken)) {
      return false;
    }

    return true;
  }

  /**
   * hasNotExpired
   *
   * @return void
   */
  protected function hasNotExpired($request) : bool
  {
    if (!isset($_SESSION['_cksal'])) return false;
    if (in_array($request->path(), $this->canExpire)) return true;

    $time = $_SESSION['_cksal'];
    $time = base64_decode($time);

    $expire = config('auth.expire.session_token');

    if(strtotime($time) < strtotime("-$expire")) {
      $this->hasExpired = true;
      return false;
    }

    return true;
  }
}
