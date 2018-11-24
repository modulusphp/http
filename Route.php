<?php

namespace Modulus\Http;

use Modulus\Http\Status;
use AtlantisPHP\Swish\Route as Swish;

class Route
{
  /**
   * $file
   *
   * @var string
   */
  private $file;

  /**
   * $middleware
   *
   * @var string
   */
  private $middleware = [];

  /**
   * $namespace
   *
   * @var string
   */
  private $namespace;

  /**
   * $prefix
   *
   * @var string
   */
  private $prefix;

  /**
   * $domain
   *
   * @var string
   */
  private $domain;

  /**
   * Add routes
   *
   * @param string $file
   * @return Route
   */
  public static function make(string $file) : Route
  {
    $router = new Route;
    $router->file = $file;

    return $router;
  }

  /**
   * Set routes middleware
   *
   * @param string $name
   * @return Route
   */
  public function middleware() : Route
  {
    $this->middleware = array_merge($this->middleware, func_get_args());
    return $this;
  }

  /**
   * Set routes namespace
   *
   * @param string $namespace
   * @return Route
   */
  public function namespace(string $namespace) : Route
  {
    $this->namespace = $namespace;
    return $this;
  }

  /**
   * Set routes prefix
   *
   * @param string $prefix
   * @return Route
   */
  public function prefix(string $prefix) : Route
  {
    $this->prefix = $prefix;
    return $this;
  }

  /**
   * Set routes domain
   *
   * @param string $domain
   * @return Route
   */
  public function domain(string $domain) : Route
  {
    $this->domain = $domain;
    return $this;
  }

  /**
   * Register routes
   *
   * @return void
   */
  public function register()
  {
    $middleware = ($this->middleware !== null) ? ['middleware' => $this->middleware] : [];
    $prefix     = ($this->prefix !== null) ? ['prefix' => $this->prefix] : [];
    $domain     = ($this->domain !== null) ? ['domain' => $this->domain] : [];

    Swish::group(array_merge($domain, $middleware, $prefix), function() {
      startphp($this->file);
    });
  }

  /**
   * Generate url from route
   *
   * @param string $name
   * @param null|array $parameters
   * @return string $url
   */
  public static function url(string $name, ?array $parameters = [])
  {
    $url = null;

    foreach (Swish::$routes as $route) {
      if ($route['name'] == $name) {
        $url = $route['pattern'];

        if ($route['required'] > 0 && ($route['required'] == count($parameters))) {
          foreach ($parameters as $name => $parameter) {
            $url = str_replace('{' . $name . '}', $parameter, $url);
          }
        }
      }
    }

    if (str_contains($url, ['{', '}'])) $url = null;

    return $url;
  }
}
