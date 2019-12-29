<?php

namespace Modulus\Http\Middleware;

use Modulus\Http\Rest;
use Modulus\Security\Auth;
use ReallySimpleJWT\TokenBuilder;
use Illuminate\Database\Eloquent\Model;

class AuthenticateWithBasicAuth
{
  /**
   * Default provider
   *
   * @var string
   */
  protected $provider = 'api';

  /**
   * Hidden fields
   *
   * @var array
   */
  protected $hidden = [
    //
  ];

  /**
   * Field musking
   *
   * * feel free to change the user value, check
   * * out the auth.php config file if you want to
   * * change the :protects value
   *
   * @var array
   */
  protected $checks = [
    'user' => 'secret', ':protects' => ':protects'
  ];

  /**
   * Handle middleware
   *
   * @param \Modulus\Http\Request $request
   * @return bool|null
   */
  public function handle($request, $continue)
  {
    if (
      $this->mustReplace($request) &&
      $this->hasBasicAuth($request) &&
      $this->hasBase64Enc($request) &&
      $this->authenticate($request)
    ) {
      $this->withAccessToken($request);
      return $continue;
    }

    $this->unauthorized($request);
  }

  /**
   * Replace :attributes with auth values
   *
   * @param \Modulus\Http\Request $request
   * @return bool
   */
  protected function mustReplace($request) : bool
  {
    try {
      foreach ($this->checks as $key => $value) {
        if (starts_with($value, ':')) {
          $this->checks[$key] = config('auth.provider.' . $this->provider . '.' . substr($key, 1));
        }

        if (starts_with($key, ':')) {
          $this->checks = array_replace_key($this->checks, $key, config('auth.provider.' . $this->provider . '.' . substr($key, 1)));
        }
      }

      return true;
    }
    catch (\Exception $e) {
      return false;
    }
  }

  /**
   * Check if request has Basic Authentication header
   *
   * @param \Modulus\Http\Request $request
   * @return bool
   */
  protected function hasBasicAuth($request) : bool
  {
    return $request->headers->has('Authorization') && starts_with($request->header('Authorization'), 'Basic ');
  }

  /**
   * Check if Basic Authentication is base64 encoded
   *
   * @param \Modulus\Http\Request $request
   * @return bool
   */
  protected function hasBase64Enc($request) : bool
  {
    return is_base64(substr($request->header('Authorization'), 6));
  }

  /**
   * Try to authenticate the request
   *
   * @param \Modulus\Http\Request $request
   * @return bool
   */
  protected function authenticate($request) : bool
  {
    $basic = base64_decode(substr($request->header('Authorization'), 6));

    $user = explode(':', $basic)[0];
    $pass = explode(':', $basic)[1];

    $response = Auth::attempt([
      $this->checks['user'] => $user,
      $this->checks[
        config('auth.provider.' . $this->provider . '.protects')
      ] => $pass
    ],
      $this->hidden,
      $this->provider
    );

    if ($response instanceof Model) {
      Auth::grant($response);
      return true;;
    }

    return false;
  }

  /**
   * addAccessToken
   *
   * @param \Modulus\Http\Request $request
   * @return \Modulus\Http\Request $request
   */
  protected function withAccessToken($request)
  {
    $expire = strtotime(config('auth.expire.access_token'));

    $token = (new TokenBuilder())->addPayload(['key' => 'pro', 'value' => $this->provider])
        ->setSecret(app()->getKey())
        ->setExpiration($expire)
        ->setIssuer(Auth::user()->id)
        ->build();

    $request->add(['access_token' => $token]);

    return $request;
  }

  /**
   * Return a 401 unauthorized response
   *
   * @param \Modulus\Http\Request $request
   * @return void
   */
  protected function unauthorized($request)
  {
    Rest::response()->json([
      'status' => 'Unauthorized',
      'code' => 401,
    ], 401);

    cancel();
  }
}
