<?php

namespace Modulus\Http\Exceptions;

use Modulus\Upstart\Exceptions\BaseException;

class ServiceUnavailableHttpException extends BaseException
{
  /**
   * {@inheritDoc}
   */
  protected $title = 'Service Unavailable';

  /**
   * Instantiate exception
   *
   * @param string $message
   * @param int $code
   * @return void
   */
  public function __construct(string $message = "Service Unavailable", int $code = 503)
  {
    parent::boot();

    $this->statusCode = $code;
    $this->message    = $message;
  }
}
