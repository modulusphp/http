<?php

namespace Modulus\Http\Controllers\Auth;

use Modulus\Http\Request;
use Modulus\Security\Hash;
use Modulus\Http\Controller;
use Modulus\Utility\Notification;
use Illuminate\Database\Eloquent\Model;
use Modulus\Framework\Auth\Notifications\MustVerifyEmail;

class RegisterController extends Controller
{
  /**
   * Default provider
   *
   * @var string
   */
  protected $provider = 'web';

  /**
   * Where to redirect users after registration.
   *
   * @var string
   */
  protected $redirectTo = '/home';

  /**
   * Get a validator for an incoming registration request.
   *
   * @return array
   */
  protected function rules() : array
  {
    return [
      'name' => 'required|string|max:255',
      'email' => [
        'required', 'string', 'email', 'max:255', new Unique('users'),
      ],
      'password' => 'required|string|min:6',
    ];
  }

  /**
   * Add new a new user
   *
   * @param \Modulus\Http\Request $request
   * @return \App\User
   */
  protected function create(Request $request) : Model
  {
    return User::create([
      'name' => $request->input('name'),
      'email' => $request->input('email'),
      'password' => Hash::make($request->input('password'))
    ]);
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
