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
  private $middleware;

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
   * Add routes
   *
   * @param string $file
   * @return void
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
   * @param string $middleware
   * @return void
   */
  public function middleware(string $middleware) : Route
  {
    $this->middleware = explode(':', $middleware);
    return $this;
  }

  /**
   * Set routes namespace
   *
   * @param string $namespace
   * @return void
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
   * @return void
   */
  public function prefix(string $prefix) : Route
  {
    $this->prefix = $prefix;
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
    $prefix = ($this->prefix !== null) ? ['prefix' => $this->prefix] : [];

    Swish::group(array_merge($middleware, $prefix), function() {
      startphp($this->file);
    });
  }
}