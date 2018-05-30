<?php

namespace ModulusPHP\Http\Requests;

use ReflectionMethod;
use JeffOchoa\ValidatorFactory;

class Request
{
  /**
   * Request::GET
   */
  const GET = 'GET';

  /**
   * Request::POST
   */
  const POST = 'POST';

  /**
   * Request::PUT
   */
  const PUT = 'PUT';

  /**
   * Request::PATCH
   */
  const PATCH = 'PATCH';

  /**
   * Request::DELETE
   */
  const DELETE = 'DELETE';

  /**
   * Request::COPY
   */
  const COPY = 'COPY';

  /**
   * Request::HEAD
   */
  const HEAD = 'HEAD';

  /**
   * Request::OPTIONS
   */
  const OPTIONS = 'OPTIONS';

  /**
   * Request::LINK
   */
  const LINK = 'LINK';

  /**
   * Request::UNLINK
   */
  const UNLINK = 'UNLINK';

  /**
   * Request::PURGE
   */
  const PURGE = 'PURGE';

  /**
   * Request::LOCK
   */
  const LOCK = 'LOCK';

  /**
   * Request::UNLOCK
   */
  const UNLOCK = 'UNLOCK';

  /**
   * Request::PROPFIND
   */
  const PROPFIND = 'PROPFIND';

  /**
   * Request::VIEW
   */
  const VIEW = 'VIEW';

  public $__method = null;

  public $__ajax = false;

  public $__data = [];

  public $__files = [];

  public $__cookies = [];

  public $validation = null;

  /**
   * Add data
   */
  public function add($args)
  {
    if (is_array($args)) {
      return $this->__data = array_merge($this->__data, $args);
    }

    return $this->__data[] = $args;
  }

  /**
   * hasInput
   * 
   * @param  string  $name
   * @return boolean
   */
  public function hasInput($name)
  {
    if (isset($this->__data[$name])) {
      return true;
    }
  }

  /**
   * input
   * 
   * @param  string  $name
   * @return
   */
  public function input($name)
  {
    return $this->__data[$name];
  }

  /**
   * hasFile
   * 
   * @param  string  $name
   * @return boolean
   */
  public function hasFile($name)
  {
    if (isset($this->__files[$name])) {
      return true;
    }

    return false;
  }

  /**
   * file
   * 
   * @param  string  $name
   * @return file
   */
  public function file($name)
  {
    return $this->__files[$name];
  }

  /**
   * hasCookie
   * 
   * @param  string  $name
   * @return boolean
   */
  public function hasCookie($name)
  {
    if (isset($this->__cookies[$name])) {
      return true;
    }
  }

  /**
   * cookie
   * 
   * @param  string  $name
   * @return file
   */
  public function cookie($name)
  {
    return $this->__cookies[$name];
  }

  /**
   * data
   * 
   * @return array
   */
  public function data()
  {
    return isset($this->__data) ? $this->__data : [];
  }

  /**
   * cookies
   * 
   * @return array
   */
  public function cookies()
  {
    return isset($this->__cookies) ? $this->__cookies : [];
  }

  /**
   * all
   * 
   * @return array
   */
  public function all()
  {
    $all = array_merge($this->__data, $this->__files);
    return $all;
  }

  /**
   * files
   * 
   * @return array
   */
  public function files()
  {
    return isset($this->__files) ? $this->__files : [];
  }

  /**
   * method
   * 
   * @return string REQUEST_METHOD
   */
  public function method()
  {
    return $_SERVER['REQUEST_METHOD'];
  }

  /**
   * isAjax
   * 
   * @return boolean
   */
  public function isAjax()
  {
    return $this->__ajax;
  }

  /**
   * If validation fails, execute callback
   * 
   * @param  closure $callback
   * @param  string  $validator
   * @return call_user_func_array
   */
  public function fails($callback = '', $validator = 'validate')
  {
    if ($this->method() == Request::GET) {
      return; // ignore
    }

    $class = debug_backtrace()[1]['object'];
    $args = debug_backtrace()[1]['args'];

    $request = debug_backtrace()[1]['args'][0];

    if (method_exists($class, $validator)) {
      $response = call_user_func_array([$class, $validator], $args);
      if ($response->errors()->toArray() != null) $this->validation = $response->errors();
    }

    if ($this->validation != null) {
      is_callable($callback) == false ?: call_user_func_array($callback, ['response' => $this->validation->toArray()]);

      if ($this->validation) {
        $_SESSION['validation.errors'] = $this->validation;
        $_SESSION['form.old'] = $request->data();
      }
      else {
        unset($_SESSION['validation.errors']);
        unset($_SESSION['form.old']);
      }

      $this->validation == false ?: die();
    }
  }

  /**
   * Try to validate an incoming request
   * @param  closure $callback
   * @param  string  $validator
   */
  public function try($callback = '', $validator = 'validate')
  {
    if ($this->method() == Request::GET) {
      return; // ignore
    }

    $class = debug_backtrace()[3]['object'];
    $args = debug_backtrace()[3]['args'];

    $request = debug_backtrace()[3]['args'][0];

    if (method_exists($class, $validator)) {
      $response = call_user_func_array([$class, $validator], $args);
      if ($response->errors()->toArray() != null) $this->validation = $response->errors();
    }

    if ($this->validation != null) {
      if ($this->validation) {
        $_SESSION['validation.errors'] = $this->validation;
        $_SESSION['form.old'] = $request->data();
      }
      else {
        unset($_SESSION['validation.errors']);
        unset($_SESSION['form.old']);
      }
    }
  }

  /**
   * If validation is successful, execute callback
   * 
   * @param  closure $callback
   * @param  string  $validator
   * @return call_user_func_array
   */
  public function success($callback = '', $validator = 'validate')
  {
    if ($this->method() == Request::GET) {
      return; // ignore
    }

    $class = debug_backtrace()[1]['object'];
    $args = debug_backtrace()[1]['args'];

    $request = debug_backtrace()[1]['args'][0];

    if (method_exists($class, $validator)) {
      $response = call_user_func_array([$class, $validator], $args);
      if ($response->errors()->toArray() != null) $this->validation = $response->errors();
    }
    else {
      return;
    }

    if ($this->validation == null) {
      is_callable($callback) == false ?: call_user_func_array($callback, [$request]);
      $this->validation == true ?: die();
    }
  }

  /**
   * Validate incoming request.
   * 
   * @param  array $validation
   * @return array
   */
  public static function validate($validation = [], $custom = [])
  {
    try {
      $request = debug_backtrace()[1]['args'][0];

      if (is_object($request)) {
        if ($validation != []) {
          $factory = new ValidatorFactory();

          $data = array_merge($request->__data, $request->__files);

          if ($data !== null && $validation !== []) {
            $response = $factory->make($data, $validation, $custom);
            return $response;
          }

          return null;
        }
      }
    }
    catch (Exception $e) {
      \App\Core\Log::error($e);
      return null;
    }
  }
}