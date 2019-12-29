<?php

namespace Modulus\Http;

class Kernel
{
  /**
   * $routeModelBinding
   *
   * @var array
   */
  protected $routeModelBinding = [];

  /**
   * $middleware
   *
   * @var array
   */
  protected $middleware = [];

  /**
   * $middlewareGroup
   *
   * @var array
   */
  protected $middlewareGroup = [];

  /**
   * $routeMiddleware
   *
   * @var array
   */
  protected $routeMiddleware = [];

  /**
   * Get application route model binding
   *
   * @return array
   */
  public function getRouteModelBinding() : array
  {
    return $this->routeModelBinding;
  }

  /**
   * Get application middleware
   *
   * @return array
   */
  public function getMiddleware() : array
  {
    return $this->middleware;
  }

  /**
   * Get application middleware group
   *
   * @return array
   */
  public function getMiddlewareGroup() : array
  {
    return $this->middlewareGroup;
  }

  /**
   * Get application route middleware
   *
   * @return array
   */
  public function getRouteMiddleware() : array
  {
    return $this->routeMiddleware;
  }
}
