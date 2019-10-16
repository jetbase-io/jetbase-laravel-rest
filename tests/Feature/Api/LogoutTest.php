<?php

namespace Tests\Feature\Api;

use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class LogoutTest extends ApiTestCase {
  use RefreshDatabase;

  public function testNotAuth() {
    $response = $this->delete('/logout', [], [
      'accept' => 'application/json'
    ]);
    $response->assertStatus(401);
  }

  public function testWithAuthorization() {
    // create user in test db
    $user = new User();
    $user->first_name = 'Test';
    $user->last_name = 'Test';
    $user->email = 'test@email.com';
    $user->password = bcrypt('test_password');
    $user->save();

    // perform login
    $response = $this->json('POST', '/login', [
      'email'    => 'test@email.com',
      'password' => 'test_password'
    ]);
    $token = $response->json('token');

    // perform logout
    $response = $this->delete('/logout', [], [
      'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(200);
  }
}
