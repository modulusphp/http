<?php

namespace Modulus\Http;

use Modulus\Support\Extendable;

class Session
{
  use Extendable;

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

  /**
  * Create a flash message
  *
  * @param string $key
  * @param mixed $value
  * @return
  */
  public static function flash(string $key, $value = null)
  {
    /**
     * Check if session already has variables.
     *
     * If variables have already been set, merge the new
     * variables with the old ones.
     */
    return $_SESSION['application']['with'] = array_merge(
                                                isset($_SESSION['application']['with']) ? $_SESSION['application']['with'] : [],
                                                [$key => $value]
                                              );
  }
}
