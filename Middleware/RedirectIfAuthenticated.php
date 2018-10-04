<?php

namespace Modulus\Http\Middleware;

use Modulus\Security\Auth;

class RedirectIfAuthenticated
{
  /**
   * Handle middleware
   *
   * @param \Modulus\Http\Request $request
   * @return bool $continue
   */
  public function handle($request, $continue) : bool
  {
    if (!Auth::isGuest()) {
      $this->redirectTo();
    }

    return $continue;
  }
}