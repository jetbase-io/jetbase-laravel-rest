<?php

namespace Tests\Feature\Api;

use App\Model\Role;
use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CurrentUserTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testAsGuest()
    {
        $response = $this->json('GET', '/users/current');
        $response->assertStatus(401); // not authorized
    }

    public function testAsNormal()
    {
        // create normal user
        $user = factory(User::class)->create(['email' => 'user@mail.com']);

        // perform login
        $token = $this->login($user->email);

        $response = $this->json('GET', '/users/current', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200); // OK

        $responseUser = $response->json();

        $this->assertIsArray($responseUser);
        $this->assertArrayHasKey('id', $responseUser);
        $this->assertEquals($user->id, $responseUser['id']);

        // check role
        $this->assertArrayHasKey('role_id', $responseUser);
        $this->assertEquals(0, $responseUser['role_id']);
    }

    public function testAsAdmin()
    {
        // create admin user
        $admin = factory(User::class)->state('admin')->create(['email' => 'admin@mail.com']);

        // perform login
        $token = $this->login($admin->email);

        $response = $this->json('GET', '/users/current', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200); // OK

        $responseUser = $response->json();

        $this->assertIsArray($responseUser);
        $this->assertArrayHasKey('id', $responseUser);
        $this->assertEquals($admin->id, $responseUser['id']);

        // check role
        $this->assertArrayHasKey('role_id', $responseUser);
        $this->assertEquals(Role::admin()->id, $responseUser['role_id']);
    }
}
