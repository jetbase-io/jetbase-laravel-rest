<?php

namespace Tests\Feature\Api;

use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CreateUserTest extends ApiTestCase {
  use RefreshDatabase;

  public function testAsGuest() {
    $response = $this->json('POST', '/users', [
      'id'         => 0,
      'first_name' => 'Test',
      'last_name'  => 'Test',
      'email'      => 'test@mail.com',
      'password'   => 'password',
      'role_id'    => '0',
    ]);

    $response->assertStatus(401);
  }

  public function testAsNormalUser() {
    // create normal user
    factory(User::class)->create(['email' => 'admin@mail.com']);

    // perform login
    $token = $this->login('admin@mail.com');

    // try create user
    $response = $this->json('POST', '/users', [
      'id'         => 0,
      'first_name' => 'Test',
      'last_name'  => 'Test',
      'email'      => 'test@mail.com',
      'password'   => 'password',
      'role_id'    => 0,
    ], [
      'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(403); // Forbidden for normal user
  }

  public function testAsAdmin() {
    // create admin
    factory(User::class)->state('admin')->create(['email' => 'admin@mail.com']);

    // perform login
    $token = $this->login('admin@mail.com');

    // try create user
    $response = $this->json('POST', '/users', [
      'id'         => 0,
      'first_name' => 'Test',
      'last_name'  => 'Test',
      'email'      => 'test@mail.com',
      'password'   => 'password',
      'role_id'    => 0,
    ], [
      'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(200); // successfully created

    // search users, must be 2: admin and just created normal user
    $searchResponse = $this->json('GET', '/users', [], [
      'Authorization' => 'Bearer ' . $token
    ]);
    $searchResponse->assertStatus(200);
    $searchUsers = $searchResponse->json();
    $this->assertIsArray($searchUsers);
    $this->assertCount(2, $searchUsers);
  }
}
