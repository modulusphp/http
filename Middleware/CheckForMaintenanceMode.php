<?php

namespace Modulus\Http\Middleware;

use Modulus\Utility\Events;

class CheckForMaintenanceMode
{
  /**
   * The URIs that should be accessible while maintenance mode is enabled.
   *
   * @var array
   */
  protected $except = [];

  /**
   * $down
   *
   * @var string
   */
  protected $down;

  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    $this->down = config('app.dir') . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'down';
  }

  /**
   * Handle middleware
   *
   * @param \Modulus\Http\Request $request
   * @return bool $continue
   */
  public function handle($request, $continue)
  {
    if ($request->isDownForMaintenance()) {
      $data = json_decode(file_get_contents($this->down), true);

      if (isset($data['allowed']) && in_array($request->ip(), (array)$data['allowed'])) {
        return $continue;
      }

      if ($this->inExceptArray($request)) {
        return $continue;
      }

      Events::trigger('maintenance', [$data['message'] ?? 'Be right back.']);
    }

    return $continue;
  }

  /**
   * Determine if the request has a URI that should be accessible in maintenance mode.
   *
   * @param \Modulus\Http\Request $request
   * @return bool
   */
  protected function inExceptArray($request)
  {
    foreach ($this->except as $except) {
      if ($request->is($except)) {
        return true;
      }
    }

    return false;
  }
}