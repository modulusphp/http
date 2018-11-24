<?php

namespace Modulus\Http\Exceptions;

use Exception;
use Modulus\Http\Status;
use Modulus\Support\Config;
use Modulus\Utility\Events;
use Modulus\Framework\Exceptions\ServerErrorsException;

class ServiceUnavailableHttpException extends Exception
{
  /**
   * $title
   *
   * @var string
   */
  protected $title = 'Service Unavailable';

  /**
   * __construct
   *
   * @return void
   */
  public function __construct(string $message = "Service Unavailable", bool $isAjax = false, int $code = 503)
  {
    $args = debug_backtrace();

    foreach (end($args) as $key => $value) {
      $this->{$key} = $value;
    }

    $this->isAjax     = $isAjax;
    $this->statusCode = $code;
    $this->message    = $message;

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
    Events::trigger('server.error', [$this->createsServerError()]);
    exit;
  }

  /**
   * Creates a new client error Exception
   *
   * @return ServerErrorsException $exception
   */
  public function createsServerError() : ServerErrorsException
  {
    return new ServerErrorsException($this->message, $this->isAjax(), $this->getStatusCode());
  }
}
