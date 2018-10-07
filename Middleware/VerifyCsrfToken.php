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
   * @param  \Modulus\Http\Request  $request
   * @return void
   */
  protected function shouldIgnore($request) : bool
  {
    $this->createUrl($request, 'except');
    if (in_array($request->path(), $this->except)) return true;

    return false;
  }

  /**
   * tokenMatches
   *
   * @param  \Modulus\Http\Request  $request
   * @return void
   */
  protected function tokenMatches($request) : bool
  {
    if (!isset( $_SESSION['_saini']) || !$_SESSION['_saini']) return false;

    $csrfToken = $request->has('csrf_token') ? $request->input('csrf_token') : ($request->hasHeader('X-CSRF-TOKEN') ? $request->header('X-CSRF-TOKEN') : null);
    $sessionToken =  $_SESSION['_saini'];

    if (!hash_equals($sessionToken, $csrfToken)) {
      return false;
    }

    return true;
  }

  /**
   * hasNotExpired
   *
   * @param  \Modulus\Http\Request  $request
   * @return void
   */
  protected function hasNotExpired($request) : bool
  {
    if (!isset($_SESSION['_cksal'])) return false;

    $this->createUrl($request, 'expire');

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

  /**
   * Create url
   *
   * @param  \Modulus\Http\Request  $request
   * @param  string  $type
   * @return void
   */
  private function createUrl($request, string $type)
  {
    $data = ($type == 'expire') ? $this->canExpire : $this->except;

    foreach($data as $i => $url) {
      if (str_contains($url, '{*}')) {
        $path = explode('/', $request->path());
        $url = explode('/', $url);

        if (count($path) == count($url)) {
          $current = [];

          foreach($url as $k => $t) {
            if ($t == $path[$k]) {
              $current[] = $t;
            } elseif ($t == '{*}') {
              $current[] = $path[$k];
            }
          }

          $data[$i] = implode('/', $current);
        }
      }
    }

    if ($type == 'expire') {
      $this->canExpire = $data;
    } else {
      $this->except = $data;
    }
  }
}
