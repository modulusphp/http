<?php

namespace Modulus\Http\Middleware;

class ConvertEmptyStringsToNull extends TransformsRequest
{
  /**
   * Convert string to null
   *
   * @param mixed $key
   * @param mixed $value
   * @return mixed
   */
  public function transform($key, $value)
  {
    return is_string($value) && $value === '' ? null : $value;
  }
}
