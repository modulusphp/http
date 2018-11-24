<?php

namespace Modulus\Http\Middleware;

class TransformsRequest
{
  /**
   * Handle an incoming request.
   *
   * @param  \Modulus\Http\Request $request
   * @param  bool $continue
   * @return bool $continue
   */
  public function handle($request, $continue) : bool
  {
    $this->clean($request);
    return $continue;
  }

  /**
   * Start the cleaning process
   *
   * @param \Modulus\Http\Request $request
   * @return void
   */
  private function clean($request)
  {
    $request->data = $this->cleanData($request->data);
  }

  /**
   * Loop through the values and pass them to the replace method
   *
   * @param array $data
   * @return array
   */
  private function cleanData(array $data) : array
  {
    foreach($data as $key => $value) {
      $data[$key] = $this->replace($key, $value);
    }

    return $data;
  }

  /**
   * Pass arrays back to the cleanData method and pass everything else to transform
   *
   * @param mixed $key
   * @param mixed $value
   * @return void
   */
  private function replace($key, $value)
  {
    if (is_array($value)) return $this->cleanData($value);

    return $this->transform($key, $value);
  }

  /**
   * Trim strings
   *
   * @param mixed $key
   * @param mixed $value
   * @return void
   */
  public function transform($key, $value)
  {
    return $value;
  }
}
