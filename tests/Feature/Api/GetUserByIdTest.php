<?php

namespace Tests\Feature\Api;

use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class GetUserByIdTest extends ApiTestCase {

  use RefreshDatabase;

  public function testAsGuest() {
    $response = $this->json('GET', '/users/1');
    $response->assertStatus(401); // not authorized
  }

  public function testNotFound() {
    $user = factory(User::class)->create();

    // perform login
    $token = $this->login($user->email);

    // try get user by not existing id
    $id = $user->id + 1;
    $response = $this->json('GET', "/users/$id", [], [
      'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(404);
  }

  public function testSuccess() {
    $user = factory(User::class)->create();

    // perform login
    $token = $this->login($user->email);

    // try get user by existing id
    $response = $this->json('GET', "/users/$user->id", [], [
      'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(200);
    $responseUser = $response->json();

    $this->assertIsArray($responseUser);
    $this->assertArrayHasKey('id', $responseUser);
    $this->assertEquals($user->id, $responseUser['id']);
  }

  public function testGetNotMe() {
    $user1 = factory(User::class)->create();
    $user2 = factory(User::class)->create();

    // perform login by user1
    $token = $this->login($user1->email);

    // try get user2
    $response = $this->json('GET', "/users/$user2->id", [], [
      'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(200);
    $responseUser = $response->json();

    $this->assertIsArray($responseUser);
    $this->assertArrayHasKey('id', $responseUser);
    $this->assertEquals($user2->id, $responseUser['id']);
  }
}
