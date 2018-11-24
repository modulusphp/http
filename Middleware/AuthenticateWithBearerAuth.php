<?php

namespace Modulus\Http\Middleware;

use Modulus\Http\Rest;
use Modulus\Security\Auth;
use ReallySimpleJWT\TokenBuilder;
use ReallySimpleJWT\TokenValidator;
use ReallySimpleJWT\Exception\TokenValidatorException;

class AuthenticateWithBearerAuth
{
  /**
   * Handle middleware
   *
   * @param \Modulus\Http\Request $request
   * @return bool $continue
   */
  public function handle($request, $continue) : bool
  {
    if (
      $this->hasBearerAuth($request) &&
      $this->authenticate($request)
    ) {
      return $continue;
    }
  }

  /**
   * Check if request has Basic Authentication header
   *
   * @param \Modulus\Http\Request $request
   * @return bool
   */
  protected function hasBearerAuth($request) : bool
  {
    if ($request->header->has('Authorization') && starts_with($request->header('Authorization'), 'Bearer ')) {
      return true;
    }

    return false;
  }

  /**
   * Try to authenticate the request
   *
   * @param \Modulus\Http\Request $request
   * @return bool
   */
  protected function authenticate($request) : bool
  {
    $bearer = substr($request->header('Authorization'), 6);
    $token  = explode(' ', $bearer)[1];

    try {
      $payLoad = $this->validateToken($token);
    }
    catch (TokenValidatorException $e) {
      $this->fails($e);
      cancel();
    }

    $userid   = $payLoad['iss'];
    $provider = $payLoad['pro'];

    $model = config('auth.provider.' . $provider . '.model');

    Auth::grant($model::where('id', $userid)->firstOrFail());

    return true;
  }

  /**
   * Validate Token
   *
   * @param mixed $token
   * @return array $payLoad
   */
  protected function validateToken($token)
  {
    $hash   = explode(':', config('app.key'))[0];
    $secret = explode(':', config('app.key'))[1];

    if ($hash == 'base64') $secret = base64_decode($secret);

    $validator = new TokenValidator;

    $validator->splitToken($token)
        ->validateExpiration()
        ->validateSignature($secret);

    $payLoad = $validator->getPayload();

    return json_decode($payLoad, true);
  }

  /**
   * Return a rest response if token validation fails
   *
   * @return void
   */
  protected function fails($exception)
  {
    $this->response($exception->getMessage(), 422);
  }

  /**
   * response message
   *
   * @return void
   */
  protected function response($message, $code) : rest
  {
    return Rest::response()->json([
      'status' => $message,
      'code' => $code
    ], $code);
  }
}
