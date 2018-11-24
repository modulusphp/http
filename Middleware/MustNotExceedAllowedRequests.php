<?php

namespace Modulus\Http\Middleware;

use Exception;
use Hashids\Hashids;
use Birke\Rememberme\Cookie\PHPCookie;
use Modulus\Framework\Exceptions\ClientErrorsException;

class MustNotExceedAllowedRequests
{
  /**
   * $except
   *
   * @var array
   */
  protected $except = [];

  /**
   * $hashid
   *
   * @var Hashids
   */
  private $hashids;

  /**
   * $cookie
   *
   * @var PHPCookie
   */
  private $cookie;

  /**
   * $request
   *
   * @var integer
   */
  private $max = 60;

  /**
   * $decay
   *
   * @var integer
   */
  private $decay;

  /**
   * $minutes
   *
   * @var integer
   */
  private $minutes;

  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    $hash   = explode(':', config('app.key'))[0];
    $secret = explode(':', config('app.key'))[1];

    if ($hash == 'base64') $secret = base64_decode($secret);

    $this->minutes = 1;
    $this->decay   = strtotime('1 minute', 0);
    $this->hashids = new Hashids($secret, 60);
    $this->cookie  = new PHPCookie('clusters', $this->decay);
  }

  /**
   * Handle an incoming request.
   *
   * @param  \Modulus\Http\Request $request
   * @param  bool $continue
   * @param  array $attributes
   * @return bool $continue
   */
  public function handle($request, $continue, $attributes) : bool
  {
    if (
      $this->autoAssign($attributes) &&
      $this->canProceed($request)
    ) {
      return $continue;
    }

    throw new ClientErrorsException($request->isAjax(), 429);
  }

  /**
   * Set request attributes
   *
   * @param array|mixed|null $attributes
   * @return void
   */
  private function autoAssign($attributes = null)
  {
    if (!is_array($attributes)) return false;

    $this->max     = isset($attributes[0]) ? $attributes[0] : $this->max;
    $this->minutes = isset($attributes[1]) ? $attributes[1] : 1;
    $this->decay   = isset($attributes[1]) ? strtotime($attributes[1] . ' minutes', 0) : $this->decay;

    return true;
  }

  /**
   * Check if request can proceed
   *
   * @param  \Modulus\Http\Request $request
   * @return bool
   */
  private function canProceed($request) : bool
  {
    $time = $this->decay - date('s');

    if ($request->cookies->has('clusters')) {
      $value = $request->cookie('clusters');
      $value = $this->hashids->decode($value)[0] - 1;

      if ($value == 0) {
        $this->withHeaders($request, $value, $time);
        return false;
      }

      $this->cookie->setExpireTime($time);
      $this->cookie->setValue($this->hashids->encode($value));

      $this->withHeaders($request, $value);

      return true;
    }

    $this->cookie->setValue($this->hashids->encode($this->max));
    $this->withHeaders($request, $this->max);

    return true;
  }

  /**
   * Set headers
   *
   * @param mixed $request
   * @param mixed $remaining
   * @return void
   */
  private function withHeaders($request, $remaining, $retry = null)
  {
    $request->headers->addMany([
      'X-RateLimit-Limit' => $this->max,
      'X-RateLimit-Remaining' => ($remaining == 0 ? $remaining : $remaining - 1)
    ]);

    if ($retry !== null) {
      $request->headers->add('X-RateLimit-Reset', $retry);
    }
  }
}
