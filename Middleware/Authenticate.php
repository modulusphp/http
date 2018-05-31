<?php

namespace ModulusPHP\Http\Middleware;

use App\Core\Auth;
use ModulusPHP\Http\Requests\Request;

class Authenticate
{
  public function handle(Request $request)
  {
    if (Auth::user() != null) {
      return true;
    }

    return redirect('/login');
  }
}
