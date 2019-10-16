<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller {
  /**
   * Handle the incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function __invoke(Request $request) {
    //$this->validate($request, [
    //  'email'    => 'required|string|email',
    //  'password' => 'required|string'
    //]);

    dd('valid1');
  }
}
