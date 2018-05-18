<?php

namespace ModulusPHP\Http\Middleware;

use App\Core\Auth;

class GuestMiddleware
{
  public function handle()
  {
    if (Auth::isGuest() != true) {
      return redirect('/');
    }
  }
}