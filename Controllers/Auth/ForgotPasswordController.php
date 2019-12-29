<?php

namespace Modulus\Http\Controllers\Auth;

use Modulus\Http\Get;
use Modulus\Http\Request;
use Modulus\Http\Session;
use Modulus\Utility\View;
use Modulus\Http\Redirect;
use Modulus\Security\Hash;
use Modulus\Framework\Password;
use Modulus\Utility\Notification;
use Modulus\Framework\Auth\RedirectsUsers;
use Modulus\Framework\Auth\MustGenerateResetPasswordToken;
use Modulus\Framework\Auth\Notifications\MustResetPassword;

class ForgotPasswordController
{
  use RedirectsUsers;
  use MustGenerateResetPasswordToken;

  /**
   * Default provider
   *
   * @var string
   */
  protected $provider = 'web';

  /**
   * Where to redirect users after password reset
   *
   * @var string
   */
  protected $redirectTo = '/login';

  /**
   * sendPasswordResetNotification
   *
   * @param string $email
   * @param string $token
   * @return array
   */
  protected function sendPasswordResetNotification(string $email, string $token)
  {
    return Notification::make(new MustResetPassword($email, $token));
  }

  /**
   * Redirect user after sending the reset password notification
   *
   * @return void
   */
  protected function redirectUser()
  {
    Redirect::back()->with('message', "A reset link has been sent to your email address.", 200);
  }

  /**
   * Redirect reset password user if the token is invalid or has expired
   *
   * @return void
   */
  protected function fails()
  {
    Redirect::back()->with('error', 'Token is invalid or has expired.', 200);
  }

  /**
   * successful
   *
   * @param mixed $verified
   * @return void
   */
  protected function success($verified)
  {
    Redirect::to($this->redirectPath())
            ->with('form.old', [
              $this->musk() => isset($verified->{$this->musk()}) ? $verified->{$this->musk()} : ''
            ])
            ->with('message', 'Your password has been successfully changed.', 200);
  }

  /**
   * Show forgot password page
   *
   * @return void
   */
  public function showForgotPasswordPage()
  {
    return View::make('app.auth.password.forgot');
  }

  /**
   * show reset password page
   *
   * @return void
   */
  public function showResetPasswordPage()
  {
    return View::make('app.auth.password.reset');
  }

  /**
   * Forgot password
   *
   * @param \Modulus\Http\Request $request
   * @return void
   */
  public function forgot(Request $request)
  {
    $request->rules = ['email' => 'required|string|email'];

    $request->validate();
    $provider = $this->provider;

    $info = $this->notify($request, $provider, $this->musk());

    $this->sendPasswordResetNotification($info['email'], $info['token']);
    $this->redirectUser();
  }

  /**
   * Reset password
   *
   * @param Modulus\Http\Request $request
   * @return void
   */
  public function resetPassword(Request $request)
  {
    if (Get::has('token')) {
      $provider = $this->provider;
      $verified = $this->verify(Get::key('token'), $provider, $this->musk());

      if (isset($verified->id)) {
        $request->rules = $this->rules();
        $request->validate();

        $this->updatePassword($request, $verified);
        return $this->success($verified);
      }
    }

    Redirect::back()->with('error', 'Token is invalid or has expired.', 200);
  }

  /**
   * Verify token
   *
   * @param string $token
   * @param string $provider
   * @param string $musked
   * @return mixed
   */
  public function verify(string $token, string $provider, string $musked)
  {
    $userToken = Password::where('token', $token)->first();

    if ($userToken == null) return false;
    $userEmail = $userToken->email;

    if ($userToken->created_at->diffInMinutes() <= config('auth.expire.magic_token')) {
      if (Session::has('_reset')) {
        if (Session::key('_reset') == $userToken->token) {
          return config("auth.provider.{$provider}.model")::where($musked, $userEmail)->first();
        }

        Session::delete('_reset');
        return false;
      }
    }

    return false;
  }

  /**
   * Update password
   *
   * @param mixed $request
   * @param mixed $user
   * @return bool
   */
  public function updatePassword($request, $user)
  {
    return $user->update([
      'password' => Hash::make($request->input('password'))
    ]);
  }

  /**
   * Get a validator for an incoming reset request.
   *
   * @return array
   */
  protected function rules() : array
  {
    return [
      'password' => 'required|confirmed|min:6',
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
