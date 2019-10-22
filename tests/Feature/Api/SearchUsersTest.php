<?php

namespace Tests\Feature\Api;


use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;

class SearchUsersTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testAsGuest()
    {
        // create 3 users
        factory(User::class, 3)->create();

        $response = $this->json('GET', '/users');

        $response->assertStatus(401);
    }

    public function testAsNormalUser()
    {
        // create 3 users
        $users = factory(User::class, 3)->create();
        /** @var \App\Model\User $user */
        $user = $users[0];

        // perform login
        $token = $this->login($user->email);

        // perform search users
        $response = $this->json('GET', '/users', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(403); // forbidden for normal user
    }

    public function testAsAdmin()
    {
        // create 9 random normal users
        factory(User::class, 9)->create();

        // create admin user
        /** @var \App\Model\User $admin */
        $admin = factory(User::class)->state('admin')->create();

        // perform login
        $token = $this->login($admin->email);

        // perform search users
        $response = $this->json('GET', '/users', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200); // forbidden for normal user
        $responseData = $response->json();
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('items', $responseData);
        $this->assertArrayHasKey('count', $responseData);

        $responseUsers = $responseData['items'];
        $this->assertIsArray($responseUsers);
        $this->assertCount(10, $responseUsers); // 9 normal + 1 admin
    }

    public function testPagination()
    {
        // create 9 random normal users
        $users = factory(User::class, 9)->create();

        // create admin user
        /** @var \App\Model\User $admin */
        $admin = factory(User::class)->state('admin')->create();

        // perform login
        $token = $this->login($admin->email);

        // perform search users
        $response = $this->json('GET', '/users', [
            'limit'  => 5,
            'offset' => 5 // second page
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200); // forbidden for normal user
        $responseData = $response->json();

        // check users
        $responseUsers = Arr::get($responseData, 'items');
        $this->assertIsArray($responseUsers);
        $this->assertCount(5, $responseUsers);

        // check id, on serverside users ordered by id
        $this->assertEquals($users[5]->id, $responseUsers[0]['id']);

        // check count
        $responseCount = Arr::get($responseData, 'count');
        $this->assertIsInt($responseCount);
        $this->assertEquals(10, $responseCount); // 9 normal users + 1 admin
    }

    public function testSearchByEmail()
    {
        // create 2 users which contains 'super' in email
        $user1 = factory(User::class)->create(['email' => 'superman@mail.com']);
        $user2 = factory(User::class)->create(['email' => 'me.super.mario@mail.com']);
        // and user without 'super' in email
        $user3 = factory(User::class)->create(['email' => 's.uper@mail.com', 'first_name' => 'Super', 'last_name' => 'Mario']);
        // also create admin
        $admin = factory(User::class)->state('admin')->create(['email' => 'admin@email.com']);

        // perform login
        $token = $this->login($admin->email);

        // perform search users
        $response = $this->json('GET', '/users', ['email' => 'super'], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200); // forbidden for normal user

        $responseUsers = $response->json('items');
        $this->assertIsArray($responseUsers);
        $this->assertCount(2, $responseUsers);

        // check ids
        $this->assertEquals($user1->id, $responseUsers[0]['id']);
        $this->assertEquals($user2->id, $responseUsers[1]['id']);
    }
}
