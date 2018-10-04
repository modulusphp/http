<?php

namespace Modulus\Http;

use Modulus\Http\Status;

class Redirect
{
  /**
   * Redirect Url
   *
   * @var string $url
   */
  private $url;

  /**
   * Redirect Variables
   *
   * @var array $with
   */
  private $with = [];

  /**
   * Redirect status code
   *
   * @var integer $code
   */
  private $code = 200;

  public function __construct(?string $url = null)
  {
    if ($url !== null) $this->url = $url; 
  }

  /**
   * to
   *
   * @param string $path
   * @param integer $code
   * @return Redirect
   */
  public static function to(?string $path = '/', ?int $code = null)
  {
    $redirect = new Redirect;
    $redirect->url = $path;

    if ($code !== null) {
      $redirect->code = $code;
      return $redirect->send();
    }

    return $redirect;
  }

  /**
   * Redirect back to the previous page
   *
   * @param mixed ?int
   * @return void
   */
  public static function back(?int $code = null)
  {
    if (isset(getallheaders()['Referer'])) {
      return Redirect::to(getallheaders()['Referer'], $code);
    }

    return new Redirect('/');
  }

  /**
   * Attach variables with redirect
   *
   * @param string $name
   * @param mixed $value
   * @param integer $code
   * @return Redirect
   */
  public function with(string $name, $value, ?int $code = null)
  {
    $this->with = array_merge([$name => $value], $this->with);

    if ($code != null) {
      $this->code = $code;
      return $this->send();
    }

    return $this;
  }

  /**
   * Allow redirect to return to current url
   *
   * @param integer $code
   * @return Redirect
   */
  public function return(?int $code = null, ?string $path = null)
  {
    $info = ((isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/') === '/' ? '' : $_SERVER['PATH_INFO']);

    if ($info !== '') $path = '?url=' . $info;

    if (count($_GET) > 0) {
      $path .= '&' . $_SERVER['QUERY_STRING'];
    }

    $this->url .= $path;

    if ($code !== null) {
      $this->code = $code;
      return $this->send();
    }

    return $this;
  }

  /**
   * Set status code
   *
   * @param integer $code
   * @return void
   */
  public function code(int $code = 200) : Redirect
  {
    $this->code = $code;
    return $this;
  }

  /**
   * Send a response
   *
   * @return mixed
   */
  public function send()
  {
    if (!array_key_exists($this->code, Status::CODE)) return false;

    if ($this->with !== null || $this->with !== []) {
      $_SESSION['application']['with'] = $this->with;
    }

    header('Location: ' . $this->url);
    exit;
  }
}