<?php

namespace Modulus\Http\Controllers\Auth;

use Modulus\Http\Controller;

class LoginController extends Controller
{
  /**
   * Default provider
   *
   * @var string
   */
  protected $provider = 'web';

  /**
   * Where to redirect users after login.
   *
   * @var string
   */
  protected $redirectTo = '/home';

  /**
   * Where to redirect users after logout.
   *
   * @var string
   */
  protected $logoutTo = '/home';

  /**
   * Hidden fields
   *
   * @var array
   */
  protected $hidden = [
    'csrf_token'
  ];

  /**
   * Get a validator for an incoming registration request.
   *
   * @return array
   */
  protected function rules() : array
  {
    return [
      'email' => 'required|string|email',
      'password' => 'required',
    ];
  }

  /**
   * Musk email
   *
   * @return string
   */
  protected function musk() : string
  {
    return 'email';
  }
}