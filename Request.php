<?php

namespace Modulus\Http;

use Closure;
use Modulus\Http\Rest;
use Modulus\Http\Redirect;
use Modulus\Request\Server;
use AtlantisPHP\Swish\Route;
use Modulus\Request\Cookies;
use Modulus\Request\Headers;
use Modulus\Utility\Validate;
use Illuminate\Database\Eloquent\Model;

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
   * Request::FILE
   */
  const FILE = 'FILE';

  /**
   * Form data
   *
   * @var $data
   */
  public $data = [];

  /**
   * Form files
   *
   * @var $data
   */
  protected $files = [];

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
  public $method;

  /**
   * $route
   *
   * @var array
   */
  public $route;

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
   * Protected names
   *
   * @var $data
   */
  protected $protected = [
    'data',
    'files',
    'cookies',
    'headers',
    'server',
    'method',
    'rules',
    'route'
  ];

  /**
   * Rules
   *
   * @var $rules
   */
  public $rules = [];

  /**
   * Construct
   *
   * @param array $data
   */
  public function __construct(array $data = [])
  {
    $this->data = $data;

    if (
      isset(getallheaders()['Content-Type']) &&
      (
        str_contains(strtolower(getallheaders()['Content-Type']), 'json') ||
        str_contains(strtolower(getallheaders()['Content-Type']), 'javascript')
      )
    ) {
      $this->data = array_merge($this->data, json_decode(file_get_contents("php://input"), true));
    }

    if ($this->data !== []) {
      foreach ($this->data as $key => $value) {
        if (!in_array($key, $this->protected)) $this->{$key} = $value;
      }
    }

    $files = array_filter($data, function($file) {
      if (isset($file['type']) && isset($file['name']) && isset($file['size'])) return $file;
    });

    $this->files   = $files;
    $this->cookies = new Cookies();
    $this->headers = new Headers();
    $this->server  = new Server();
    $this->path    = (str_contains($_SERVER['REQUEST_URI'], '?')) ? explode('?', ($_SERVER['REQUEST_URI']))[0] : $_SERVER['REQUEST_URI'];
    $this->url     = $_SERVER['REQUEST_URI'];
    $this->method  = $_SERVER['REQUEST_METHOD'];
    $this->isAjax  = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                            ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

  }

  /**
   * Add items
   *
   * @param  array  $data
   * @return Request
   */
  public function add(array $data = []) : Request
  {
    $this->data = array_merge($this->data, $data);

    $files = array_filter($data, function($file) {
      if (isset($file['type']) && isset($file['name']) && isset($file['size'])) return $file;
    });

    $this->files = array_merge($this->files, $files);
    return $this;
  }

  /**
   * Request has input
   *
   * @param  string $name
   * @return bool
   */
  public function has($name) : bool
  {
    if (isset($this->data[$name])) return true;
    return false;
  }

  /**
   * Request has file
   *
   * @param  string  $name
   * @return bool
   */
  public function hasFile($name) : bool
  {
    if (isset($this->files[$name])) return true;
    return false;
  }

  /**
   * Get request input
   *
   * @param  string $name
   * @return mixed
   */
  public function input($name)
  {
    return $this->data[$name];
  }

  /**
   * Get request file
   *
   * @param  string $name
   * @return array
   */
  public function file($name)
  {
    return $this->files[$name];
  }

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
   * Get request data
   *
   * @return array $this->data
   */
  public function data() : array
  {
    return $this->data;
  }

  /**
   * Get request data
   *
   * @return array $this->data
   */
  public function all() : array
  {
    return $this->data;
  }

  /**
   * Get request files
   *
   * @return array $this->files
   */
  public function files() : array
  {
    return $this->files;
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
   * Return path
   *
   * @return string $this->path
   */
  public function path() : string
  {
    return $this->path;
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
   * Get current route
   *
   * @return object
   */
  public function route() : object
  {
    return (object)Route::current();
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

  /**
   * Get client ip address
   *
   * @return void
   */
  public function ip($return_type = null, $ip_addresses = [])
  {
    $ip_elements = array(
      'HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED_FOR',
      'HTTP_X_FORWARDED', 'HTTP_FORWARDED',
      'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_CLUSTER_CLIENT_IP',
      'HTTP_X_CLIENT_IP', 'HTTP_CLIENT_IP',
      'REMOTE_ADDR'
    );

    foreach ( $ip_elements as $element ) {
      if(isset($_SERVER[$element])) {
        if ( !is_string($_SERVER[$element]) ) {
          // Log the value somehow, to improve the script!
          continue;
        }
        $address_list = explode(',', $_SERVER[$element]);
        $address_list = array_map('trim', $address_list);
        // Not using array_merge in order to preserve order
        foreach ( $address_list as $x ) {
          $ip_addresses[] = $x;
        }
      }
    }
    if (count($ip_addresses) == 0) {
      return false;
    } elseif ($return_type === 'array') {
      return $ip_addresses;
    } elseif ($return_type === 'single' || $return_type === null) {
      return $ip_addresses[0];
    }
  }

  /**
   * Run validation
   *
   * @return mixed
   */
  public function validate(?Closure $closure = null)
  {
    $response = validate::make($this->data(), isset($this->rules) ? $this->rules : []);

    if (is_callable($closure)) {
      $custom = call_user_func($closure, $response);

      if ($custom instanceOf Model) return $custom;

      if (is_array($custom)) {
        foreach($custom as $key => $unique) {
          $response->errors()->add($key, $unique);
        }
      }
    }

    if (count($response->errors()) > 0 || $response->fails()) {
      if ($this->headers->has('Content-Type') &&  str_contains(strtolower($this->headers->get('Content-Type')), ['json', 'javascript'])) {
        Rest::response()->json($response->errors()->toArray(), 422);
        die();
      }

      $url = ($this->server->has('HTTPS') ? 'https://' : 'http://') . $this->server->get('HTTP_HOST');

      if ($this->server->has('HTTP_ORIGIN') && $this->server->get('HTTP_ORIGIN') == $url) {
        $referer = $this->server->get('HTTP_ORIGIN');

        Redirect::to($referer)
            ->with('validation.errors', $response->errors())
            ->with('form.old', $this->all())
            ->code(302)
            ->send();
      } else {
        Rest::response()->json($response->errors()->toArray(), 422);
        die();
      }
    }
  }
}
