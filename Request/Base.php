<?php

namespace Modulus\Http\Request;

use Modulus\Request\Server;
use Modulus\Request\Cookies;
use Modulus\Request\Headers;
use Modulus\Support\Extendable;

class Base
{
  use Extendable;

  /**
   * Request::GET
   */
  const GET      = 'GET';

  /**
   * Request::POST
   */
  const POST     = 'POST';

  /**
   * Request::PUT
   */
  const PUT      = 'PUT';

  /**
   * Request::PATCH
   */
  const PATCH    = 'PATCH';

  /**
   * Request::DELETE
   */
  const DELETE   = 'DELETE';

  /**
   * Request::COPY
   */
  const COPY     = 'COPY';

  /**
   * Request::HEAD
   */
  const HEAD     = 'HEAD';

  /**
   * Request::OPTIONS
   */
  const OPTIONS  = 'OPTIONS';

  /**
   * Request::LINK
   */
  const LINK     = 'LINK';

  /**
   * Request::UNLINK
   */
  const UNLINK   = 'UNLINK';

  /**
   * Request::PURGE
   */
  const PURGE    = 'PURGE';

  /**
   * Request::LOCK
   */
  const LOCK     = 'LOCK';

  /**
   * Request::UNLOCK
   */
  const UNLOCK   = 'UNLOCK';

  /**
   * Request::PROPFIND
   */
  const PROPFIND = 'PROPFIND';

  /**
   * Request::FILE
   */
  const FILE     = 'FILE';

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
    'route',
    'isAjax',
    'path',
    'url'
  ];

  /**
   * Create request instance
   *
   * @param array|null $data
   */
  public function __construct(?array $data = [])
  {
    $this->data = $data = array_merge($_POST, $_FILES);

    try {
      $json = json_decode(file_get_contents("php://input"), true);
      $this->data = array_merge($this->data, is_array($json) ? $json : []);
    } catch (Exception $e) {
      // do nothing
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

    if ($this->rules == [] && count(is_array($this->rules()) ? $this->rules() : []) > 0) {
      $this->rules = $this->rules();
    }

    if ($this->messages == [] && count(is_array($this->messages()) ? $this->messages() : []) > 0) {
      $this->messages = $this->messages();
    }

    /**
     * Run the validate method if request has rules
     */
    if (count(is_array($this->rules) ? $this->rules : [])) $this->validate();
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
}
