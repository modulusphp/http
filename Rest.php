<?php

namespace ModulusPHP\Http;

use ModulusPHP\Http\Status;

class Rest
{
  private $data = [];
  private $code = 200;
  private $headers = ['Content-Type: application/json'];
  private $message;

  public function __construct($msg = null)
  {
    if ($msg != null) $this->message = $msg;
  }

  /**
   * Build a response
   * 
   * @param  string $message
   * @return object $rest
   */
  public static function response(string $message = null, $code = null)
  {
    $rest = new Rest;
    $rest->message = $message;
    
    if ($code != null) {
      $rest->code = $code;
      $rest->send();
      return;
    }

    return $rest;
  }

  /**
   * Attach json data
   * 
   * @param  array  $array
   * @return object $this
   */
  public function json(array $array = [], $code = null)
  {
    $this->data = $array;
    
    if ($code != null) {
      $this->code = $code;
      $this->send();
      return;
    }

    return $this;
  }

  /**
   * Set status code
   * 
   * @param  integer $code
   * @return object  $this
   */
  public function code($code = 200)
  {
    $this->code = $code;
    return $this;
  }

  /**
   * Set response header's
   * 
   * @param  array  $array
   * @return object $this
   */
  public function withHeaders(array $array, $code = null)
  {
    foreach($array as $field => $i) {
      array_push($this->headers, $field .': '. $i);
    }

    if ($code != null) {
      $this->code = $code;
      $this->send();
      return;
    }

    return $this;
  }

  /**
   * Send a response
   * 
   * @return
   */
  public function send()
  {
    if (!array_key_exists($this->code, Status::CODE)) return false;

    foreach($this->headers as $header) {
      header($header);
    }

    header('HTTP/1.1 '.$this->code);

    $response = array();
    if ($this->message != null) $response['status'] = $this->message;
    if ($this->data != []) $response = array_merge($response, $this->data);

    echo json_encode($response);
  }
}