<?php

namespace Modulus\Http\Request;

trait HasRequest
{
  /**
   * Application cookies
   *
   * @var Cookies
   */
  public $cookies;

  /**
   * Application headers
   *
   * @var Headers
   */
  public $headers;

  /**
   * $server
   *
   * @var Server
   */
  public $server;

  /**
   * Request method
   *
   * @var $method
   */
  protected $method;

  /**
   * Request type
   *
   * @var $isAjax
   */
  protected $isAjax;

  /**
   * $path
   *
   * @var string
   */
  protected $path;

  /**
   * $url
   *
   * @var string
   */
  protected $url;

   /**
   * Get request cookie
   *
   * @param  string $name
   * @return mixed
   */
  public function cookie($name)
  {
    return $this->cookies->get($name);
  }

  /**
   * Get server
   *
   * @param mixed $name
   * @return void
   */
  public function server($name)
  {
    return $this->server->get($name);
  }

  /**
   * Get request header
   *
   * @param  string $name
   * @return mixed
   */
  public function header($name)
  {
    return $this->headers->get($name);
  }

  /**
   * Request cookies
   *
   * @return array $this->cookies
   */
  public function cookies() : array
  {
    return $this->cookies->all();
  }

  /**
   * Request headers
   *
   * @return array $this->headers
   */
  public function headers() : array
  {
    return $this->headers->all();
  }

   /**
   * Get request method
   *
   * @return string $this->method
   */
  public function method() : string
  {
    return $this->method;
  }

  /**
   * Check if method equals value
   *
   * @param string $method
   * @return bool
   */
  public function isMethod(string $method) : bool
  {
    return strtolower($this->method) == strtolower($method);
  }

  /**
	 * Check if current request is xmlhttp or http
	 *
	 * @return bool $this->isAjax
	 */
  public function isAjax() : bool
  {
		return $this->isAjax;
  }

  /**
   * is
   *
   * @param string $url
   * @return void
   */
  public function is(string $url)
  {
    if ($this->path() == $url || $this->url() == $url) return true;
    return false;
  }

  /**
   * Return url
   *
   * @return string $this->url
   */
  public function url() : string
  {
    return $this->url;
  }

  /**
   * Return path
   *
   * @return string $this->path
   */
  public function path() : string
  {
    return $this->path;
  }

  /**
   * Check if app is down for maintenance
   *
   * @return bool
   */
  public function isDownForMaintenance() : bool
  {
    if (file_exists(config('app.dir') . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'down')) {
      return true;
    }

    return false;
  }
}
