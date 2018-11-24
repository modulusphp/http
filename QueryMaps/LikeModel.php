<?php

namespace Modulus\Http\QueryMaps;

use Modulus\Utility\RouteQuery;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class LikeModel extends RouteQuery
{
  /**
   * Handle Query Map
   *
   * @param EloquentModel $model
   * @param mixed $field
   * @param mixed $value
   * @return EloquentModel
   */
  protected function handle(EloquentModel $model, $field, $value) : EloquentModel
  {
    return $model::where($field, 'like', '%' . $value . '%')->first() ?? $model;
  }
}
