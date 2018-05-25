<?php

namespace ModulusPHP\Http;

class Session
{
  public static function has($key)
  {
    if (isset($_SESSION[$key])) {
      return true;
    }

    return false;
  }

  public static function delete($key)
  {
    unset($_SESSION[$key]);
  }

  public static function key($key, $value = null)
  {
    if ($value == null) {
      return $_SESSION[$key];
    }

    $_SESSION[$key] = $value;
  }
}