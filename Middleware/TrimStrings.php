<?php

namespace Modulus\Http\Middleware;

class TrimStrings extends TransformsRequest
{
  /**
   * The names of the attributes that should not be trimmed.
   *
   * @var array
   */
  protected $except = [
    //
  ];

  /**
   * Trim strings
   *
   * @param mixed $key
   * @param mixed $value
   * @return mixed
   */
  public function transform($key, $value)
  {
    if (in_array($key, $this->except)) {
      return $value;
    }

    return is_string($value) ? trim($value) : $value;
  }
}
