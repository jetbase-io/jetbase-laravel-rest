<?php

namespace Tests\Feature\Api;

use App\Model\Role;
use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UpdateUserTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testAsGuest()
    {
        // create user
        $user = factory(User::class)->create(['first_name' => 'Initial']);

        $response = $this->json('PUT', "/users/$user->id", [
            'first_name' => 'Changed'
        ]);

        $response->assertStatus(401);
    }

    public function testTryUpdateNotMe()
    {
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

    public function testTryUpdateMe()
    {
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

    public function testTryUpdateAsAdmin()
    {
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

    public function testNormalUserWantUpdateRoleToAdmin()
    {
        $user = factory(User::class)->create();
        $token = $this->login($user->email);

        // try update role to admin
        $response = $this->json('PUT', "/users/{$user->id}", [
            'role_id' => Role::admin()->id
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // all ok, but role not assigned
        $dbUser = User::find($user->id);
        $this->assertNotEquals($dbUser->role_id, Role::admin()->id);
    }

    public function testAdminWantChangeRoleForUser()
    {
        // create admin
        $admin = factory(User::class)->state('admin')->create();

        // create normal user without any role
        $user = factory(User::class)->create();

        // create some role
        $role = new Role();
        $role->name = 'some_role';
        $role->save();

        // login by admin
        $token = $this->login($admin->email);

        // admin tries change user's role
        $response = $this->json('PUT', "/users/$user->id", [
            'role_id' => $role->id
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // retrieve user from db
        $user = User::findOrFail($user->id);
        // check role changed
        $this->assertEquals($role->id, $user->role_id);
    }

    /**
     * Assert error when tries change user's email, if this email already taken by another user
     */
    public function testUpdateEmailWhenTaken()
    {
        // create admin
        $admin = factory(User::class)->state('admin')->create();

        // create 2 normal users
        $user1 = factory(User::class)->create(['email' => 'email1@example.com']);
        $user2 = factory(User::class)->create(['email' => 'email2@example.com']);

        // login by admin
        $token = $this->login($admin->email);

        // admin tries change user2 email
        $response = $this->json('PUT', "/users/$user2->id", [
            'email' => 'email1@example.com'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(400);

        $errorMessage = $response->json('message');
        $this->assertStringContainsString('email already used', $errorMessage);

        // check users emails not changed
        $dbUsers = User::noRole()->orderBy('id')->get();
        $dbUser1 = $dbUsers[0];
        $dbUser2 = $dbUsers[1];
        $this->assertEquals($dbUser1->email, $user1->email);
        $this->assertEquals($dbUser2->email, $user2->email);
    }
}
