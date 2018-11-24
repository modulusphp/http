<?php

namespace Modulus\Http\Middleware;

use Modulus\Http\Status;
use Modulus\Utility\Events;
use Modulus\Framework\Exceptions\RouteFailedException;

class MustProtectRoutes
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
    $http = ($request->server->has('HTTPS') && $request->server('HTTPS') != 'off') ? 'https://' : 'http://';

    if (
      $request->server->has('HTTP_REFERER') &&
      starts_with($request->server->http_referer, $http . $request->headers->host)
      ) {
      return $continue;
    }

    throw new RouteFailedException($request->isAjax(), 403);
  }
}
