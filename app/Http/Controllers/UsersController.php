<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Model\Role;
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
    $this->authorize('create', User::class);

    $data = $request->json()->all();

    // base validation
    Validator::make($data, [
      'id'         => 'required|int|in:0',
      'first_name' => 'required|string',
      'last_name'  => 'required|string',
      'email'      => 'required|string|email',
      'password'   => 'required|string|min:8',
      'role_id'    => 'required|int|in:0',
    ])->validate();

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

  /**
   * Search registered users.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function search(Request $request) {
    $data = $request->json()->all();
    Validator::make($data, [
      'email'     => 'nullable|string',
      'page'      => 'nullable|int|min:1',
      'page_size' => 'nullable|int|min:1',
    ]);

    $this->authorize('viewAny', User::class);

    $query = User::query()->orderBy('id');

    // filter by email
    if ($qEmail = Arr::get($data, 'email')) {
      $query->where('email', 'like', $qEmail);
    }

    // pagination
    $page_size = Arr::get($data, 'page_size');
    if (!is_null($page_size)) {
      $page = Arr::get($data, 'page', 1);
      $offset = ($page - 1) * $page_size;
      $query->limit($page_size)->offset($offset);
    }

    $users = $query->get();

    return response()->json(UserResource::collection($users));
  }

  /**
   * Returns current auth user.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function current() {
    $authUser = auth()->user();
    return response()->json(UserResource::make($authUser));
  }

  /**
   * Get user by id.
   *
   * @param int $user_id
   * @return \Illuminate\Http\JsonResponse
   */
  public function getById(int $user_id) {
    $user = User::find($user_id);
    if (!$user) {
      return response()->json([
        'error' => 'User not found'
      ], 404);
    }

    return response()->json(UserResource::make($user));
  }

  /**
   * Update user by id.
   *
   * @param Request $request
   * @param int $user_id
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(Request $request, int $user_id) {

    // check user exists
    /** @var \App\Model\User $user */
    $user = User::find($user_id);
    if (!$user) {
      return response()->json(['error' => 'User not found'], 404);
    }

    // check permission: self or admin
    /** @var \App\Model\User $authUser */
    $authUser = auth()->user();
    if (!$authUser->can('update', $user)) {
      return response()->json(['error' => 'Invalid user supplied'], 400);
    }

    // validate body params
    $data = $request->json()->all();
    Validator::make($data, [
      'first_name' => 'nullable|string',
      'last_name'  => 'nullable|string',
      'email'      => 'nullable|string|email',
      'role_id'    => 'nullable|int|in:0',
    ])->validate();

    // update only if passed
    if (Arr::has($data, 'first_name')) $user->first_name = Arr::get($data, 'first_name');
    if (Arr::has($data, 'last_name')) $user->last_name = Arr::get($data, 'last_name');
    if (Arr::has($data, 'email')) $user->email = Arr::get($data, 'email');

    // change role: only if role_id passed, and is admin, and role exists
    if (Arr::has($data, 'role_id') && $authUser->can('changeRole', $user)) {
      $role_id = Arr::get($data, 'role_id');
      /** @var \App\Model\Role $role */
      if ($role = Role::find($role_id)) {
        $user->role_id = $role->id;
      }
    }

    $user->save();

    return response()->json([
      'success' => true
    ]);
  }
}
