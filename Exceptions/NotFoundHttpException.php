<?php

namespace Modulus\Http\Exceptions;

use Modulus\Upstart\Exceptions\BaseException;

class NotFoundHttpException extends BaseException
{
  /**
   * {@inheritDoc}
   */
  protected $title = 'Not found';

  /**
   * {@inheritDoc}
   */
  protected $message = "Not Found!";

  /**
   * Instantiate exception
   *
   * @param int $code Status code
   * @return void
   */
  public function __construct(int $code = 404)
  {
    parent::boot();

    $this->statusCode = $code;
  }
}
