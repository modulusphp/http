<?php

namespace Modulus\Http\Middleware;

use Modulus\Http\Redirect;
use Modulus\Security\Auth;

class Authenticate
{
  /**
   * Handle middleware
   *
   * @param \Modulus\Http\Request $request
   * @return bool $continue
   */
  public function handle($request, $continue) : bool
  {
    if (Auth::isGuest()) {
      $this->redirectTo();
    }

    return $continue;
  }

  /**
   * Get the path the user should be redirected to when they are not authenticated.
   *
   * @return void
   */
  protected function redirectTo()
  {
    return Redirect::to('/login')->return(302);
  }
}
