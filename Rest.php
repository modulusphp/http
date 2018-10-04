<?php

namespace Modulus\Http;

use Modulus\Http\Status;

class Rest
{
  /**
   * $data
   *
   * @var array
   */
  private $data = [];

  /**
   * $code
   *
   * @var integer
   */
  private $code = 200;

  /**
   * $headers
   *
   * @var array
   */
  private $headers = ['Content-Type: application/json'];

  /**
   * $message
   *
   * @var string
   */
  private $message;

  /**
   * __construct
   *
   * @param mixed $msg
   * @return void
   */
  public function __construct($msg = null)
  {
    if ($msg != null) $this->message = $msg;
  }

  /**
   * Build a response
   *
   * @param  string  $message
   * @param  integer $code
   * @param  array   $headers
   * @return Rest
   */
  public static function response(string $message = null, int $code = null, array $headers = []) : Rest
  {
    $rest = new Rest;
    $rest->message = $message;

    if ($headers != []) {
      foreach($headers as $field => $i) {
        array_push($rest->headers, $field .': '. $i);
      }
    }

    if ($code != null) {
      $rest->code = $code;
      return $rest->send();
    }

    return $rest;
  }

  /**
   * Attach json data
   *
   * @param  array   $array
   * @param  integer $code
   * @return Rest
   */
  public function json(array $array = [], int $code = null) : Rest
  {
    $this->data = $array;

    if ($code != null) {
      $this->code = $code;
      return $this->send();
    }

    return $this;
  }

  /**
   * Set status code
   *
   * @param  integer $code
   * @return Rest
   */
  public function code(int $code = 200) : Rest
  {
    $this->code = $code;
    return $this;
  }

  /**
   * Set response header's
   *
   * @param  array  $array
   * @return Rest
   */
  public function withHeaders(array $array, int $code = null) : Rest
  {
    foreach($array as $field => $i) {
      array_push($this->headers, $field .': '. $i);
    }

    if ($code != null) {
      $this->code = $code;
      return $this->send();
    }

    return $this;
  }

  /**
   * Send a response
   *
   * @return Rest
   */
  public function send() : Rest
  {
    foreach($this->headers as $header) {
      header($header);
    }

    header('HTTP/1.1 '.$this->code);

    $response = array();
    if ($this->message != null) $response['status'] = $this->message;
    if ($this->data != []) $response = array_merge($response, $this->data);

    echo json_encode($response);
    return $this;
  }
}