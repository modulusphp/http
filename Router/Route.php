<?php

namespace ModulusPHP\Http\Router;

use ReflectionMethod;
use App\Http\HttpFoundation;
use ModulusPHP\Http\Requests\Request;

class Route
{
  static public $status = 404;
  static public $executed = false;

  /**
   * get
   * 
   * @param  string  $pattern
   * @param  string  $callback
   * @param  boolean $ajax
   * @return
   */
  public static function get($pattern, $callback, $ajax = false)
  {
    if (self::search(['GET'], $pattern, $callback, $ajax) == true) {
      static::$executed = true;
    }
  }

  /**
   * post
   * 
   * @param  string  $pattern
   * @param  string  $callback
   * @param  boolean $ajax
   * @return
   */
  public static function post($pattern, $callback, $ajax = false)
  {
    if (self::search(['POST'], $pattern, $callback, $ajax) == true) {
      static::$executed = true;
    }
  }

  /**
   * put (untested)
   * 
   * @param  string  $pattern
   * @param  string  $callback
   * @param  boolean $ajax
   * @return
   */
  public static function put($pattern, $callback, $ajax = false)
  {
    if (self::search(['PUT'], $pattern, $callback, $ajax) == true) {
      static::$executed = true;
    }
  }

  /**
   * patch (untested)
   * 
   * @param  string  $pattern
   * @param  string  $callback
   * @param  boolean $ajax
   * @return
   */
  public static function patch($pattern, $callback, $ajax = false)
  {
    if (self::search(['PATCH'], $pattern, $callback, $ajax) == true) {
      static::$executed = true;
    }
  }

  /**
   * delete (untested)
   * 
   * @param  string  $pattern
   * @param  string  $callback
   * @param  boolean $ajax
   * @return
   */
  public static function delete($pattern, $callback, $ajax = false)
  {
    if (self::search(['DELETE'], $pattern, $callback, $ajax)  == true) {
      static::$executed = true;
    }
  }

  /**
   * search
   * 
   * @param  array   $methods
   * @param  string  $pattern
   * @param  string  $callback
   * @param  boolean $ajax
   * @return
   */
  private static function search($methods, $pattern, $callback, $ajax)
  {
    $pattern = startsWith($pattern, '/') == false ? '/'.$pattern : $pattern ;

    // grouped routes support
    if (isset(debug_backtrace()[3]['args'][0]['prefix'])) {
      $pattern = startsWith($pattern, '/') == false ? '/'.debug_backtrace()[3]['args'][0]['prefix'].$pattern : debug_backtrace()[3]['args'][0]['prefix'].$pattern;
    }

    if (isset(debug_backtrace()[3]['args'][0]['auth'])) {
      if (debug_backtrace()[3]['args'][0]['auth'] == true && is_string($callback)) {
        $callback = 'Auth\\'.$callback;
      }
    }

    $middleware = null;
    if (isset(debug_backtrace()[3]['args'][0]['middleware'])) {
      $middleware = debug_backtrace()[3]['args'][0]['middleware'];
    }

    // if there's already a matched route, don't run this
    if (static::$status == 200) {
      return;
    }

    if (!in_array(strtoupper($_SERVER['REQUEST_METHOD']), $methods)) {
      return false;
    }

    // untested
    if ($ajax && isset($_SERVER['HTTP_X_REQUESTED_WITH']) == true) {
      return strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) === 'XMLHTTPREQUEST' ? true : false;
    }
    else if ($ajax && isset($_SERVER['HTTP_X_REQUESTED_WITH']) != true) {
      return false;
    }

    $len = strlen($_SERVER['REQUEST_URI']);
    $uri = substr($_SERVER['REQUEST_URI'], -1) == '/' ? substr($_SERVER['REQUEST_URI'], 0, $len - 1) : $_SERVER['REQUEST_URI'] ;
    $pattern = substr($pattern, -1) == '/' ? substr($pattern, 0, strlen($pattern) - 1) : $pattern ;

    $pattern_regex = preg_replace("/\{(.*?)\}/", "(?P<$1>[\w-]+)", $pattern);
    $pattern_regex = "#^" . trim($pattern_regex, "/") . "$#";

    preg_match($pattern_regex, trim($uri, "/"), $matches);

    if ($matches && static::$status == 404 || $uri == $pattern && static::$status == 404) {
      static::$status = 200;
    }

    $controller;

    if ($matches && is_callable($callback) == false) {
      $controller = explode('@', $callback)[0];
      $action = isset(explode('@', $callback)[1]) ? explode('@', $callback)[1] : 'index';

      if (!file_exists('../app/Http/Controllers/'.str_replace('\\', '/', $controller).'.php')) {
        \App\Core\Log::error($controller.' doesn\'t exist');
        static::$status = 404;
        return true;
      }

      $controllerClass = 'App\\Http\\Controllers\\'.str_replace('/', '\\', $controller);
      $controller = new $controllerClass;
    }

    if ($pattern != "/") {
      foreach($matches as $key => $value) {
        if (is_numeric($key)) {
          unset($matches[$key]);
        }
      }
    }

    if (static::$executed == false) {
      $modifiedPattern = explode('/', filter_var(rtrim(substr($pattern, 1),'/'), FILTER_SANITIZE_URL));
      $modifiedUrl = self::parseUrl();

      $count = 0;
      foreach ($modifiedPattern as $key => $value) {
        if (isset($value[0])) {
          if ($value[0] == '{') {
            if (isset($modifiedUrl[$count])) {
              unset($modifiedPattern[$key]);
              unset($modifiedUrl[$count]);
            }
          }
          $count++;
        }
      }

      if ($matches && is_callable($callback) && $modifiedPattern == $modifiedUrl) {
        self::middleware($middleware, $matches, $ajax);
        $matches = self::reflect($controller, $action, $matches, $ajax);
        
        call_user_func($callback, (object)$matches);
        return true;
      }
      else if ($uri == $pattern && is_callable($callback) && $modifiedPattern == $modifiedUrl) {
        self::middleware($middleware, $matches, $ajax);
        $matches = self::reflect($controller, $action, $matches, $ajax);
        
        call_user_func($callback, (object)$matches);
        return true;
      }
      else if ($uri == $pattern && $modifiedPattern == $modifiedUrl) {
        self::middleware($middleware, $matches, $ajax);
        $matches = self::reflect($controller, $action, $matches, $ajax);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
          if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $matches);
            return true;
          }

          self::isError('POST', $action, explode('@', $callback)[0]);
          return true;
        }
        else if ($_SERVER['REQUEST_METHOD'] == "GET") {
          if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $matches);
            return true;
          }

          self::isError('GET', $action, explode('@', $callback)[0]);
          return true;
        }
        else {
          if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $matches);
            return true;
          }

          self::isError("Any", $action, explode('@', $callback)[0]);
          return true;
        }
      }
      else if ($matches && is_string($callback) && $modifiedPattern == $modifiedUrl) {
        self::middleware($middleware, $matches, $ajax);
        $matches = self::reflect($controller, $action, $matches, $ajax);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
          if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $matches);
            return true;
          }

          self::isError("POST", $action, explode('@', $callback)[0]);
          return true;
        }
        else if ($_SERVER['REQUEST_METHOD'] == "GET") {
          if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $matches);
            return true;
          }

          self::isError("GET", $action, explode('@', $callback)[0]);
          return true;
        }
        else {
          if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $matches);
            return true;
          }

          self::isError("Any", $action, explode('@', $callback)[0]);
          return true;
        }
      }
    }
  }

  /**
   * reflect
   * 
   * @param  class  $controller
   * @param  method $action
   * @param  array  $matches
   * @param  bool   $ajax
   * @return array  $matches
   */
  public static function reflect($controller, $action, $matches, $ajax)
  {
    if (method_exists($controller, $action) == false) {
      return;
    }

    $r = new ReflectionMethod(new $controller(), $action);

    $args = $r->getParameters();
    $count = $r->getNumberOfParameters();
    $required = $r->getNumberOfRequiredParameters();

    $index = 0;
    $noArgs = false;

    if ($matches == null) {
      $noArgs = true;
      $matches = $args;
    }

    // if (count($matches) < $required) {
    // 
    // }

    foreach($args as $param) {
      $class = '\\'.$param->getType();

      if (class_exists($class)) {
        $where = array_keys($matches)[$index];
        $value = array_values($matches)[$index];

        if ($class == "\ModulusPHP\Http\Requests\Request") {
          $req = new Request;
          if ($ajax == true) {
            $req->__ajax = true;
          }

          $req->__data = array_merge($_POST, $_GET);
          $req->__files = $_FILES;
          $req->__cookies = $_COOKIE;

          if ($noArgs == false) {
            $previous = array_prev_key($where, $matches);

            if ($previous == null) {
              $matches = array_merge([$req], $matches);
            }
            else {
              $matches = array_insert_after($matches, $previous, [$req]);
            }
          }
          else {
            $matches[$where] = $req;
          }
        }
        else if (strpos($class, '\Models') !== false) {
          if ($where != null && is_integer($where) == false) {
            $model = (new $class)->where($where, $value)->first();
          }
          else {
            $model = null;
          }

          $matches[$where] = $model == null ? new $class : $model;
        }
        else {
          $matches[$where] = new $class($matches[$value]);
        }

      }

      $index++;
    }

    return $matches;
  }

  /**
   * parseUrl
   * 
   * @return string  $url
   */
  private static function parseUrl()
  {
    return $url =  explode('/', filter_var(rtrim(substr($_SERVER['REQUEST_URI'], 1),'/'), FILTER_SANITIZE_URL));
  }

  /**
   * middleware
   * 
   * @param  array $routes
   * @param  array $matches
   * @param  bool  $ajax
   * @return void
   */
  private static function middleware($routes = null, $matches, $ajax)
  {
    if ($routes == null) {
      return;
    }

    if (is_string($routes)) {
      foreach(HttpFoundation::$Middleware as $middlewareName => $middleroute) {
        if ($middlewareName == $routes) {
          $matches = Self::reflect($middleroute, 'handle', $matches, $ajax);
          $middleroute = new $middleroute;

          call_user_func_array([$middleroute, 'handle'], $matches);
        }
      }
      
      return;
    }

    foreach($routes as $i) {
      foreach(HttpFoundation::$Middleware as $middlewareName => $middleroute) {
        if ($middlewareName == $i) {
          $matches = Self::reflect($middleroute, 'handle', $matches, $ajax);
          $middleroute = new $middleroute;

          if (call_user_func_array([$middleroute, 'handle'], $matches) == false) {
            return;
          }
        }
      }
    }
  }

  /**
   * Return a 500 Internal Error
   * 
   * @param  REQUEST_METHOD $requestMethod
   * @param  method $action
   * @param  controller $controller
   * @return void;
   */
  private static function isError($requestMethod = "Any", $action, $controller)
  {
    header('HTTP/1.0 500 Internal Error');
    if ($requestMethod == "POST") {
      echo \ModulusPHP\Http\Controllers\Controller::response(array('error' => '@'.$action.' doesn\'t exist in '.$controller));
      \App\Core\Log::error('@'.$action.' doesn\'t exist in '.$controller);
    }
    else if ($requestMethod == "GET") {
      \ModulusPHP\Touch\View::error(500);
      \App\Core\Log::error('@'.$action.' doesn\'t exist in '.$controller);
    }
    else {
      echo \ModulusPHP\Http\Controllers\Controller::response('@'.$action.' doesn\'t exist in '.$controller);
      \App\Core\Log::error('@'.$action.' doesn\'t exist in '.$controller);
    }
  }
}