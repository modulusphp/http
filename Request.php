<?php

namespace Modulus\Http;

use Modulus\Http\Request\Base;
use Modulus\Http\Request\HasInput;
use Modulus\Http\Request\HasRequest;
use Modulus\Http\Request\HasValidation;

final class Request extends Base
{
  use HasInput;
  use HasRequest;
  use HasValidation;
}
