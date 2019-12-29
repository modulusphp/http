<?php

namespace Modulus\Http;

final class UrlGenerator
{
  /**
   * Generate url
   *
   * @param string $path
   * @param mixed ?bool
   * @return string
   */
  public static function get(string $path, bool $uri = true) : string
  {
    $path = substr($path, 0, 1) != '/' ? '/' . $path : $path;
    $root = isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER['SCRIPT_NAME'] : '';
    $dir  = pathinfo($root)['dirname'];

    if (isset($_SERVER['HTTP_HOST'])) {
      $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
      $url = $http . $_SERVER['HTTP_HOST'] . ($dir == '/' ? '' : $dir) . $path;
    } else {
      $url = ($uri ? config('app.url') : '') . $path;
    }

    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
      if (str_contains($url, '?')) $url .= '&' . $_SERVER['QUERY_STRING'];
      if (!str_contains($url, '?')) $url .= '?' . $_SERVER['QUERY_STRING'];
    }

    return $url;
  }
}
