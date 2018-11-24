<?php

namespace Modulus\Http;

class Middleware
{
  /**
   * $all
   *
   * @var array
   */
  public $all = [];

  /**
   * $only
   *
   * @var array
   */
  public $only = [];

  /**
   * $except
   *
   * @var array
   */
  public $except = [];

  /**
   * __constuct
   *
   * @return void
   */
  public function __construct()
  {
    $this->all = func_get_args()[0];
  }

  /**
   * Assign middleware to specific methods
   *
   * @return void
   */
  public function only()
  {
    $this->only = func_get_args();
  }

  /**
   * Assign middleware to specific methods
   *
   * @return void
   */
  public function except()
  {
    $this->except = func_get_args();
  }
}
