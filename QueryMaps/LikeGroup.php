<?php

namespace Modulus\Http\QueryMaps;

use Illuminate\Database\Eloquent\Collection;
use Modulus\Utility\{Groupable, RouteQuery};

class LikeGroup extends RouteQuery
{
  /**
   * Handle Query Map
   *
   * @param Groupable $group
   * @param mixed $field
   * @param mixed $value
   * @return Collection
   */
  protected function handle(Groupable $group, $field, $value, $name) : Groupable
  {
    return $group->assign($group->model()::where($field, 'like', '%' . $value . '%')->get() ?? new Collection);
  }
}
