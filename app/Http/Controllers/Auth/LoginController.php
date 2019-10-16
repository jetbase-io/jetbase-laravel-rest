<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller {
  /**
   * Handle the incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function __invoke(Request $request) {
    $data = $request->json()->all();
    Validator::make($data, [
      'email'    => 'required|string|email',
      'password' => 'required|string'
    ])->validate();

    $credentials = request(['email', 'password']);

    if (!$token = auth()->attempt($credentials)) {
      return response()->json([
        'error' => 'Invalid email/password supplied'
      ], 400);
    }

    return $this->respondWithToken($token);
  }

  private function respondWithToken($token) {
    $ttl = auth()->factory()->getTTL(); // in minutes
    $expires = now()->addMinutes($ttl);

    return response()->json(['token' => $token])->withHeaders([
      // max requests per hour
      'X-Rate-Limit'    => config('api.rate_limit'),
      // date format: https://swagger.io/docs/specification/data-models/data-types/
      'X-Expires-After' => $expires->format(DATE_RFC3339),
    ]);
  }
}
