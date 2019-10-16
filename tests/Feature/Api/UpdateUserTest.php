<?php

namespace Tests\Feature\Api;

use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UpdateUserTest extends ApiTestCase {
  use RefreshDatabase;

  public function testAsGuest() {
    // create user
    $user = factory(User::class)->create(['first_name' => 'Initial']);

    $response = $this->json('PUT', "/users/$user->id", [
      'first_name' => 'Changed'
    ]);

    $response->assertStatus(401);
  }

  public function testTryUpdateNotMe() {
    $user1 = factory(User::class)->create(['first_name' => 'Alex']);
    $user2 = factory(User::class)->create(['first_name' => 'Lukas']);

    // authorize by user1
    $token = $this->login($user1->email);

    // try update user2
    $response = $this->json('PUT', "/users/$user2->id", [
      'first_name' => 'Hacker Was Here'
    ], [
      'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(400); // not allow change not self
  }

  public function testTryUpdateMe() {
    $user = factory(User::class)->create(['first_name' => 'Alex']);

    // authorize by user
    $token = $this->login($user->email);

    // try update user
    $response = $this->json('PUT', "/users/$user->id", [
      'first_name' => 'MyNewFirstName'
    ], [
      'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(200);

    // fetch user by id
    $response2 = $this->json('GET', "/users/$user->id", [], [
      'Authorization' => 'Bearer ' . $token
    ]);
    $response2->assertStatus(200);
    $responseUser = $response2->json();
    $this->assertEquals($user->id, $responseUser['id']);
    $this->assertEquals('MyNewFirstName', $responseUser['first_name']);
  }

  public function testTryUpdateAsAdmin() {
    $user = factory(User::class)->create(['first_name' => 'Alex']);
    $admin = factory(User::class)->state('admin')->create(['first_name' => 'Admin']);

    // authorize by admin
    $token = $this->login($admin->email);

    // try update user
    $response = $this->json('PUT', "/users/$user->id", [
      'first_name' => 'NewFirstName'
    ], [
      'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(200);

    // fetch user by id
    $response2 = $this->json('GET', "/users/$user->id", [], [
      'Authorization' => 'Bearer ' . $token
    ]);
    $response2->assertStatus(200);
    $responseUser = $response2->json();
    $this->assertEquals($user->id, $responseUser['id']);
    $this->assertEquals('NewFirstName', $responseUser['first_name']);
  }
}