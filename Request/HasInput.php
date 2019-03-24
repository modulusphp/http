<?php

namespace Modulus\Http\Request;

use Modulus\Support\File;

trait HasInput
{
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
   * Grab selected fields.
   *
   * @param array $array
   * @return array $data
   */
  public function only(array $array)
  {
    $data = [];

    foreach($array as $field) {
      if ($this->has($field)) {
        $data[$field] = $this->data[$field];
      }
    }

    return $data;
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
   * @param  string|null $disk
   * @return array
   */
  public function file($name, $disk = null)
  {
    return File::make($this->files[$name], $disk);
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
}
