<?php

namespace Modulus\Http;

use Modulus\Http\Status;
use Modulus\Utility\View;
use Modulus\Support\Extendable;

class Rest
{
  use Extendable;

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
  public static function response(string $message = null, int $code = null, array $headers = [])
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
      header('HTTP/1.1 '.$rest->code);
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
  public function json(array $array = [], int $code = null)
  {
    $this->data = $array;

    if ($code != null) {
      $this->code = $code;
      header('HTTP/1.1 '.$this->code);
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
    header('HTTP/1.1 '.$this->code);
    return $this;
  }

  /**
   * Set response header's
   *
   * @param  array  $array
   * @return Rest
   */
  public function withHeaders(array $array, int $code = null)
  {
    foreach($array as $field => $i) {
      array_push($this->headers, $field .': '. $i);
    }

    if ($code != null) {
      $this->code = $code;
      header('HTTP/1.1 '.$this->code);
      return $this->send();
    }

    return $this;
  }

  /**
   * Return a view response
   *
   * @param string $path
   * @param array $data
   * @param bool $return
   * @return mixed
   */
  public function view(string $path, array $data = [], bool $return = false)
  {
    if ($this->headers !== ['Content-Type: application/json']) {
      foreach($this->headers as $header) {
        header($header);
      }
    }

    return View::make($path, $data, $return);
  }

  /**
   * Reads the requested portion of a file and sends its contents to the client with the appropriate headers.
   * This HTTP_RANGE compatible read file function is necessary for allowing streaming media to be skipped around in.
   *
   * @param string $location
   * @param string $filename
   * @param string $mimeType
   * @return
   *
   * @link https://gist.github.com/benvium/3749316
   */
  public function download(string $location, ?string $filename = null, ?array $headers = [])
  {
    if (!file_exists($location)) {
      header("HTTP/1.1 404 Not Found");
      return;
    }

    $size	= filesize($location);
    $time	= date('r', filemtime($location));
    $fm		= @fopen($location, 'rb');

    if (!$fm) {
      header("HTTP/1.1 505 Internal server error");
      return;
    }

    $filename = ($filename == null) ? basename($location) : $filename;
    $mimeType =  mime_content_type($location);

    $begin	= 0;
    $end	  = $size - 1;

    if (isset($_SERVER['HTTP_RANGE'])) {
      if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
        $begin = intval($matches[1]);
        if (!empty($matches[2])) {
          $end= intval($matches[2]);
        }
      }
    }

    if ($headers == [] || $headers == null) {
      if (isset($_SERVER['HTTP_RANGE'])) {
        header('HTTP/1.1 206 Partial Content');
      }
      else {
        header('HTTP/1.1 200 OK');
      }

      header("Content-Type: $mimeType");
      header('Cache-Control: public, must-revalidate, max-age=0');
      header('Pragma: no-cache');
      header('Accept-Ranges: bytes');
      header('Content-Length:' . (($end - $begin) + 1));

      if (isset($_SERVER['HTTP_RANGE'])) {
        header("Content-Range: bytes $begin-$end/$size");
      }

      header("Content-Disposition: inline; filename=$filename");
      header("Content-Transfer-Encoding: binary");
      header("Last-Modified: $time");
    } else {
      foreach($headers as $header) {
        header($header);
      }
    }

    $cur	= $begin;
    fseek($fm, $begin, 0);

    while(!feof($fm) && $cur <= $end && (connection_status() == 0)) {
      print fread($fm, min(1024 * 16, ($end - $cur) + 1));
      $cur += 1024 * 16;
    }
  }

  /**
   * Send a response
   *
   * @return Rest
   */
  public function send()
  {
    foreach($this->headers as $header) {
      header($header);
    }

    $response = array();

    if ($this->message != null) {
      $response = $this->message;
    } elseif ($this->data != []) {
      $response = array_merge($response, $this->data);
    }

    if (is_array($response)) {
      echo json_encode($response);
      return;
    }

    echo $response;
  }
}
