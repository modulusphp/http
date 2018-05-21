<?php

namespace ModulusPHP\Http\Middleware;

use App\Core\Auth;

class Authenticate
{
  public function handle()
  {
    if (Auth::isGuest() == true) {
      return true;
    }

    return redirect();
  }
}
