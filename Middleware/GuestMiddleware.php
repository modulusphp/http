<?php

namespace ModulusPHP\Http\Middleware;

use App\Core\Auth;
use ModulusPHP\Http\Requests\Request;

class GuestMiddleware
{
  public function handle(Request $request)
  {
    if (Auth::isGuest() != true) {
      return redirect('/');
    }
  }
}