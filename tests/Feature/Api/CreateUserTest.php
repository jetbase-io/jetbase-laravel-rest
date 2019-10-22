<?php

namespace Tests\Feature\Api;

use App\Model\Role;
use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CreateUserTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testAsGuest()
    {
        $response = $this->json('POST', '/users', [
            'first_name' => 'Test',
            'last_name'  => 'Test',
            'email'      => 'test@mail.com',
            'password'   => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function testAsNormalUser()
    {
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
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(403); // Forbidden for normal user
    }

    public function testAsAdmin()
    {
        // create admin
        factory(User::class)->state('admin')->create(['email' => 'admin@mail.com']);

        // create test role
        $role = new Role();
        $role->name = 'some_role';
        $role->save();

        // perform login
        $token = $this->login('admin@mail.com');

        // try create user
        $createResponse = $this->json('POST', '/users', [
            'id'                    => 0,
            'first_name'            => 'Test',
            'last_name'             => 'Test',
            'email'                 => 'test@mail.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role_id'               => $role->id
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $createResponse->assertStatus(200); // successfully created

        // check return id
        $createData = $createResponse->json();
        $this->assertIsArray($createData);
        $this->assertArrayHasKey('id', $createData);
        $this->assertIsInt($createData['id']);
        $createdUserId = $createData['id'];

        // search users, must be 2: admin and just created normal user
        $searchResponse = $this->json('GET', '/users', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $searchResponse->assertStatus(200);
        $searchUsers = $searchResponse->json();
        $this->assertIsArray($searchUsers);
        $this->assertCount(2, $searchUsers);

        // check created user
        $user = $searchUsers[1]; // users ordered by id, [0] is admin, [1] - created via API
        $this->assertEquals($user['id'], $createdUserId);
        $this->assertEquals($user['first_name'], 'Test');
        $this->assertEquals($user['last_name'], 'Test');
        $this->assertEquals($user['email'], 'test@mail.com');
        $this->assertEquals($user['role_id'], $role->id);
    }
}
