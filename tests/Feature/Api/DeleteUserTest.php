<?php

namespace Tests\Feature\Api;

use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DeleteUserTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testAsGuest()
    {
        // create user
        $user = factory(User::class)->create();
        $response = $this->delete("/users/$user->id", [], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(401); // not authorized
    }

    public function testDeleteNotSelf()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $token = $this->login($user1->email);

        // user1 tries delete user2
        $response = $this->delete("/users/$user2->id", [], [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(403);
    }

    public function testDeleteSelf()
    {
        $user = factory(User::class)->create();
        $token = $this->login($user->email);

        // user tries delete self
        $response = $this->delete("/users/$user->id", [], [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(204);

        // check `users` table empty
        $this->assertTrue(User::count() === 0);
    }

    public function testDeleteUserAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class)->state('admin')->create();
        $token = $this->login($admin->email);

        // admin tries delete user
        $response = $this->delete("/users/$user->id", [], [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(204);

        // check only admin in `users` table
        $dbUsers = User::all();
        $this->assertTrue($dbUsers->count() === 1);
        $this->assertEquals($dbUsers[0]->id, $admin->id);
    }
}
