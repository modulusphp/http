<?php

namespace Modulus\Http;

use Modulus\Support\Extendable;

class Get
{
  use Extendable;

  /**
   * Check if query contains key
   *
   * @param mixed $key
   * @return bool
   */
  public static function has($key) : bool
  {
    if (isset($_GET[$key])) return true;
    return false;
  }

  /**
   * Delete query key
   *
   * @param mixed $key
   * @return void
   */
  public static function delete($key)
  {
    unset($_GET[$key]);
  }

  /**
   * Return query key
   *
   * @param mixed $key
   * @return mixed
   */
  public static function key($key)
  {
    return $_GET[$key];
  }
}
