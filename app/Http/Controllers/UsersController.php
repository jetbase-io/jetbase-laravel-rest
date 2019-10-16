<?php

namespace App\Http\Controllers;

use App\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller {

  /**
   * Create an user.
   *
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
  public function create(Request $request) {
    // todo authorize admin
    $data = $request->json()->all();

    // base validation
    $validator = Validator::make($data, [
      'id'         => 'required|int|in:0',
      'first_name' => 'required|string',
      'last_name'  => 'required|string',
      'email'      => 'required|string|email',
      'password'   => 'required|string|min:8',
      'role_id'    => 'required|int|in:0',
    ]);
    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Bad Request',
        'errors'  => $validator->errors()->messages()
      ], 400);
    }

    // check email already taken
    $email = Arr::get($data, 'email');
    $emailTaken = User::whereEmail($email)->exists();
    if ($emailTaken) {
      return response()->json([
        'success' => false,
        'message' => 'This email already used by another user: ' . $email,
      ], 400);
    }

    $user = new User();
    $user->first_name = Arr::get($data, 'first_name');
    $user->last_name = Arr::get($data, 'last_name');
    $user->email = Arr::get($data, 'email');
    $user->password = bcrypt(Arr::get($data, 'password'));
    $user->save();

    return response()->json([
      'success' => true,
    ]);
  }
}
