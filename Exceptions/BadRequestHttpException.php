<?php

namespace Modulus\Http\Exceptions;

use Modulus\Upstart\Exceptions\BaseException;

class BadRequestHttpException extends BaseException
{
  /**
   * {@inheritDoc}
   */
  protected $title = 'Bad Request';

  /**
   * {@inheritDoc}
   */
  protected $message = "Method Not Allowed";

  /**
   * Instantiate exception
   *
   * @param int $code Status code
   * @return void
   */
  public function __construct(int $code = 405)
  {
    parent::boot();

    $this->statusCode = $code;
  }
}
