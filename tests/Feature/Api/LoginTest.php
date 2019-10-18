<?php

namespace Tests\Feature\Api;

use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

class LoginTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testEmptyBody()
    {
        $response = $this->post("/login");
        $response->assertStatus(400);
    }

    public function testEmptyDB()
    {
        $response = $this->post("/login", [
            'email'    => 'some@email.com',
            'password' => 'password'
        ]);
        $response->assertStatus(400);
    }

    public function testInvalidCredentials()
    {
        // create user in test db
        $user = new User();
        $user->first_name = 'Test';
        $user->last_name = 'Test';
        $user->email = 'test@email.com';
        $user->password = bcrypt('test_password');
        $user->save();

        // call api
        $response = $this->json('POST', '/login', [
            'email'    => '1test@email.com',
            'password' => '1test_password'
        ]);
        $response->assertStatus(400);
    }

    public function testValidCredentials()
    {

        // create user in test db
        $user = new User();
        $user->first_name = 'Test';
        $user->last_name = 'Test';
        $user->email = 'test@email.com';
        $user->password = bcrypt('test_password');
        $user->save();

        // call api
        $response = $this->json('POST', '/login', [
            'email'    => 'test@email.com',
            'password' => 'test_password'
        ]);
        $response->assertStatus(200);

        $token = $response->json('token');
        $this->assertIsString($token);

        $response->assertHeader('X-Rate-Limit', config('api.rate_limit'));
        $response->assertHeader('X-Expires-After');

        $expires = $response->headers->get('X-Expires-After');
        $this->assertTrue(validRFC3339Date($expires));

        // check in future
        $expires = Carbon::parse($expires);
        $this->assertTrue($expires->isFuture());
    }
}
