<?php

namespace ModulusPHP\Http\Controllers;

use App\Core\Auth;
use Illuminate\Database\Capsule\Manager as DB;

class BaseController
{
  /**
   * Model
   * 
   * @param  string $model
   * @return model $model
   */
  public function model($model)
  {
    require_once '../app/Models/' .$model . '.php';
    return new $model();
  }

  /**
   * View
   * 
   * @param  string $view
   * @param  array $data
   * @return view
   */
  public function view($view, $data = [])
  {
    return \ModulusPHP\Touch\View::make($view, $data);
  }

  /**
   * Response
   * 
   * @param  array $response
   * @return json $response
   */
  public function response(Array $response)
  {
    header('content-type: application/json');
    echo json_encode($response);
  }

  /**
   * Back
   * 
   * @param  string $fallback
   * @return string
   */
  public function back($fallback = '/')
  {
    if (isset($_SERVER['HTTP_REFERER'])) {
        return header('Location: '.$_SERVER['HTTP_REFERER']);
    }

    echo '<script>window.location = "'.$fallback.'";</script>';
  }

  /**
   * Redirect
   * 
   * @param  string $location
   * @return header
   */
  public function redirect($location = '/')
  {
    return header('Location: '.$location);
  }

  /**
   * isAuthorized
   * 
   * @return redirect
   */
  public function isAuthorized()
  {
    if (Auth::isGuest() == true) {
      if ($_SERVER['REQUEST_URI'] != '/login') {
        return $this->redirect('/login');
      }
    }
  }
}