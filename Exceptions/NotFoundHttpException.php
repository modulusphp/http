<?php

namespace Modulus\Http\Exceptions;

use Exception;
use Modulus\Http\Status;
use Modulus\Support\Config;
use Modulus\Utility\Events;
use Modulus\Framework\Exceptions\ClientErrorsException;

class NotFoundHttpException extends Exception
{
  /**
   * $title
   *
   * @var string
   */
  protected $title = 'Not found';

  /**
   * $message
   *
   * @var string
   */
  protected $message = "Not Found!";

  /**
   * __construct
   *
   * @return void
   */
  public function __construct(bool $isAjax = false, int $code = 404)
  {
    $args = debug_backtrace();

    foreach (end($args) as $key => $value) {
      $this->{$key} = $value;
    }

    $this->isAjax     = $isAjax;
    $this->statusCode = $code;

    Status::set($this->getStatusCode());
  }

  /**
   * Check if request is ajax or not
   *
   * @return bool
   */
  public function isAjax() : bool
  {
    return $this->isAjax;
  }

  /**
   * Return status code
   *
   * @return int
   */
  public function getStatusCode() : int
  {
    return $this->statusCode;
  }

  /**
   * Returns page title
   *
   * @return mixed
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Check if application can render error
   *
   * @return void
   */
  private function render() : void
  {
    $this->handle();
  }

  /**
   * Handle the error
   *
   * @return void
   */
  public function handle() : void
  {
    Events::trigger('client.error', [$this->createsClientError()]);
    exit;
  }

  /**
   * Creates a new client error Exception
   *
   * @return void
   */
  public function createsClientError()
  {
    return new ClientErrorsException($this->isAjax(), $this->getStatusCode());
  }
}
