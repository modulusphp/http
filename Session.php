<?php

namespace Modulus\Http;

class Session
{
  /**
   * Check if session has key
   *
   * @param mixed $key
   * @return void
   */
  public static function has($key)
  {
    if (isset($_SESSION[$key])) return true;

    return false;
  }

  /**
   * Delete session key
   *
   * @param mixed $key
   * @return void
   */
  public static function delete($key)
  {
    unset($_SESSION[$key]);
  }

  /**
   * Set or get session key
   *
   * @param mixed $key
   * @param mixed $value
   * @return void
   */
  public static function key($key, $value = null)
  {
    if ($value == null) return $_SESSION[$key];

    $_SESSION[$key] = $value;
  }
}