<?php

namespace Modulus\Http\Mocks;

use Modulus\Http\Rest;
use Modulus\Http\Request;
use Modulus\Http\Redirect;
use JeffOchoa\ValidatorFactory;
use Illuminate\Validation\Validator;

trait ValidatesRequests
{
  /**
   * Validates requests and redirect or return rest response
   *
   * @param \Modulus\Http\Request $request
   * @param array $rules
   * @param array $messages
   * @param array $customAttributes
   * @return mixed
   */
  public function validateWithHttp(Request $request, array $rules, array $messages = [], array $customAttributes = [])
  {
    $response = (new ValidatorFactory)->make($request->all(), $rules, $messages, $customAttributes);

    if (count($response->errors()) > 0 || $response->fails()) {

      if (
        $request->headers->has('Content-Type') &&
        str_contains(strtolower($request->headers->get('Content-Type')), 'json')
      ) {
        cancel(Rest::response()->json($response->errors()->toArray(), 422));
      }

      $url = ($request->server->has('HTTPS') ? 'https://' : 'http://') . $request->server->get('HTTP_HOST');

      if (
        $request->server->has('HTTP_ORIGIN') &&
        $request->server->get('HTTP_ORIGIN') == $url
      ) {
        $referer = $request->headers->get('Referer');

        return Redirect::to($referer)
                  ->with('validation.errors', $response->errors())
                  ->with('form.old', $request->all())
                  ->code(302)
                  ->send();
      }

      cancel(Rest::response()->json($response->errors()->toArray(), 422));
    }

    return $response;
  }

  /**
   * Validates requests and return factory
   *
   * @param \Modulus\Http\Request $request
   * @param array $rules
   * @param array $messages
   * @param array $customAttributes
   * @return Validator
   */
  public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = []) : Validator
  {
    return (new ValidatorFactory)->make($request->all(), $rules, $messages, $customAttributes);
  }
}
