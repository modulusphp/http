<?php

namespace Modulus\Http;

use Modulus\Request\Server;
use Modulus\Request\Cookies;
use Modulus\Request\Headers;
use Modulus\Http\Request\Base;
use Modulus\Http\Request\HasInput;
use Modulus\Http\Request\HasRequest;
use Modulus\Http\Request\HasValidation;

class Request extends Base
{
  use HasInput;
  use HasRequest;
  use HasValidation;

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
      $json = json_decode(file_get_contents("php://input"), true);
      $this->data = array_merge($this->data, is_array($json) ? $json : []);
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

    /**
     * Run the validate method if request has rules, and
     * the csrf token is present.
     */
    if (
      count(is_array($this->rules) ? $this->rules : [])  > 0 &&
      ($this->has('csrf_token') || $this->headers->has('X-CSRF-TOKEN'))
    ) {
      $this->validate();
    }
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
