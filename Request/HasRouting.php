<?php

namespace Modulus\Http\Request;

use AtlantisPHP\Swish\Route;

trait HasRouting
{
  /**
   * $route
   *
   * @var array
   */
  public $route;

  /**
   * Get current route
   *
   * @return object
   */
  public function route() : object
  {
    return (object)Route::current();
  }
}
