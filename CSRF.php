<?php

namespace Modulus\Http;

use Modulus\Http\Request;
use Modulus\Utility\Events;

class CSRF
{
  /**
   * Generate csrf token
   *
   * @return string
   */
  public static function generate()
  {
    $_SESSION['_cksal'] = self::genTimeStamp();
    $_SESSION['_saini'] = bin2hex(random_bytes(30));

    return $_SESSION['_saini'];
  }

  /**
   * Generate timestamp
   *
   * @return string
   */
  public static function genTimeStamp()
  {
    return base64_encode(date('Y-m-d H:i:s'));
  }
}
