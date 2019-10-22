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

        $responseData = $response->json();
        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('rate_limit', $responseData);
        $this->assertArrayHasKey('expires_after', $responseData);

        // check token
        $token = $responseData['token'];
        $this->assertIsString($token);

        // check rate_limit is int
        $rateLimit = $responseData['rate_limit'];
        $this->assertIsInt($rateLimit);

        // check rate_limit value
        $this->assertEquals($rateLimit, config('api.rate_limit'));

        // check expires_after valid format
        $expiresAfter = $responseData['expires_after'];
        $this->assertTrue(validRFC3339Date($expiresAfter));

        // check expires_after is in future
        $expiresAfter = Carbon::parse($expiresAfter);
        $this->assertTrue($expiresAfter->isFuture());
    }
}
