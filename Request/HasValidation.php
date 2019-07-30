<?php

namespace Modulus\Http\Request;

use Closure;
use Modulus\Http\Rest;
use Modulus\Http\Redirect;
use JeffOchoa\ValidatorFactory;
use Illuminate\Database\Eloquent\Model;

trait HasValidation
{
  /**
   * Rules
   *
   * @var $rules
   */
  public $rules = [];

  /**
   * Error messages
   *
   * @var $messages
   */
  public $messages = [];

  /**
   * Form rules
   *
   * @return array
   */
  public function rules() : array
  {
    return [];
  }

  /**
   * Form error messages
   *
   * @return array
   */
  public function messages() : array
  {
    return [];
  }

  /**
   * Run validation
   *
   * @return mixed
   */
  public function validate(?Closure $closure = null)
  {
    /**
     * Create a new validation factory
     */
    $factory = new ValidatorFactory();
    $response = $factory->make($this->data(), isset($this->rules) ? $this->rules : [], isset($this->messages) ? $this->messages : []);

    if (is_callable($closure)) {
      $custom = call_user_func($closure, $response);

      if ($custom instanceOf Model) return $custom;

      if (is_array($custom)) {
        foreach($custom as $key => $unique) {
          $response->errors()->add($key, $unique);
        }
      }
    }

    if (count($response->errors()) > 0 || $response->fails()) {
      if ($this->headers->has('Content-Type') &&  str_contains(strtolower($this->headers->get('Content-Type')), 'json')) {
        Rest::response()->json($response->errors()->toArray(), 422);
        die();
      }

      $url = ($this->server->has('HTTPS') ? 'https://' : 'http://') . $this->server->get('HTTP_HOST');

      if ($this->server->has('HTTP_ORIGIN') && $this->server->get('HTTP_ORIGIN') == $url) {
        $referer = $this->headers->get('Referer');

        Redirect::to($referer)
            ->with('validation.errors', $response->errors())
            ->with('form.old', $this->all())
            ->code(302)
            ->send();
      } else {
        Rest::response()->json($response->errors()->toArray(), 422);
        die();
      }
    }
  }
}
