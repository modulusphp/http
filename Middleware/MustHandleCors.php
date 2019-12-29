<?php

namespace Modulus\Http\Middleware;

use Modulus\Support\Config;

class MustHandleCors
{
  /**
   * Handle an incoming request.
   *
   * @param  \Modulus\Http\Request $request
   * @param  bool $continue
   * @return bool $continue
   */
  public function handle($request, $continue) : bool
  {
    if ($request->server->has('HTTP_ORIGIN')) {
      $this->cors($request);
    }

    if ($request->isMethod('options')) exit;

    return $continue;
  }

  /**
   * Handles cors.
   *
   * @param  \Modulus\Http\Request $requests
   * @return void
   */
  private function cors($request)
  {
    $origin  = $request->server->http_origin;
    $allowed = Config::get('cors.allowedOrigins');

    if (in_array('*', $allowed) || in_array($origin, $allowed) ) {
      $this->withHeaders($request);
    }
  }

  /**
   * Adds cors headers.
   *
   * @param  \Modulus\Http\Request $request
   * @return void
   */
  private function withHeaders($request)
  {
    $headers = Config::get('cors.allowedHeaders');
    $methods = Config::get('cors.allowedMethods');
    $exposed = Config::get('cors.exposedHeaders');
    $maxAge  = Config::get('cors.maxAge');

    $request->headers->addMany([
      'Access-Control-Allow-Credentials' => Config::get('cors.supportsCredentials'),
      'Access-Control-Allow-Origin' => '*',
      'Access-Control-Allow-Headers' => implode(', ', $headers),
      'Access-Control-Allow-Methods' => implode(', ', $methods),
    ]);

    if (count($exposed) > 0) {
      $request->headers->add('Access-Control-Exposed-Headers', implode(', ', $exposed));
    }

    if ($maxAge > 0) {
      $request->headers->add('Access-Control-Max-Age', $maxAge);
    }
  }
}
