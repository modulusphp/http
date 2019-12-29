<?php

namespace Modulus\Http;

use Modulus\Hibernate\Session;

class CSRF
{
  /**
   * Generate csrf token
   *
   * @return string
   */
  public static function generate()
  {
    Session::flash()->set('_session_stamp', self::genTimeStamp());
    Session::flash()->set('_session_token', bin2hex(random_bytes(30)));

    return Session::flash()->get('_session_token');
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
