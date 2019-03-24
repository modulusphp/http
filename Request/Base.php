<?php

namespace Modulus\Http\Request;

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
}
