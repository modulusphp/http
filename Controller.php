<?php

namespace Modulus\Http;

use Modulus\Utility\View;
use Modulus\Support\Extendable;

class Controller
{
  use Extendable;

  /**
   * $middleware
   *
   * @var string
   */
  public $middleware;

  /**
   * Set middleware
   *
   * @return void
   */
  public function middleware()
  {
    $this->middleware = new Middleware(func_get_args());
    return $this->middleware;
  }

  /**
   * Create view
   *
   * @param string $name
   * @param ?array $data
   * @return
   */
  public function view(string $name, ?array $data = [])
  {
    return View::make($name, $data ?? []);
  }

  /**
   * Check if a model value has already been used or not
   *
   * @param Eloquent $model
   * @param array $fields
   * @return array $response
   */
  public function search(string $model, array $fields) : ?array
  {
    $response = array();
    foreach($fields as $param => $value) {
      $check = $model::where($param, $value)->first();

      if ($check != null) {
        $response = array_merge($response, array($param => "The $param has already been taken."));
      }
    }

    return $response;
  }
}
