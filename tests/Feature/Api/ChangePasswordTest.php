<?php

namespace Tests\Feature\Api;

use App\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class ChangePasswordTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testNoAuth()
    {
        // create user
        $user = factory(User::class)->create([
            'password' => bcrypt('test_password')
        ]);

        // guest tries change password for user
        $response = $this->json('PUT', "/users/{$user->id}/password", [
            'password_old' => 'test_password',
            'password'     => 'super_secret'
        ]);

        $response->assertStatus(401);
    }

    public function testChangeMyPasswordEmptyBody()
    {
        // create user
        $user = factory(User::class)->create([
            'password' => bcrypt('test_password')
        ]);

        $token = $this->login($user->email, 'test_password');

        // user tries change password with empty body
        $response = $this->json('PUT', "/users/{$user->id}/password", [
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(400);
    }

    public function testInvalidOldPassword()
    {
        // create user
        $user = factory(User::class)->create([
            'password' => bcrypt('test_password')
        ]);

        // login
        $token = $this->login($user->email, 'test_password');

        // user tries change password with invalid old password
        $response = $this->json('PUT', "/users/{$user->id}/password", [
            'password_old'          => 'test_password_invalid',
            'password'              => 'super_secret',
            'password_confirmation' => 'super_secret',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(403);
    }

    public function testInvalidPasswordConfirmation()
    {
        // create user
        $user = factory(User::class)->create([
            'password' => bcrypt('test_password')
        ]);

        // login
        $token = $this->login($user->email, 'test_password');

        // user tries change password with invalid password confirmation
        $response = $this->json('PUT', "/users/{$user->id}/password", [
            'password_old'          => 'test_password_invalid',
            'password'              => 'super_secret',
            'password_confirmation' => 'super_secret_123',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(400);

        // check error message
        $errorMessage = Arr::get($response->json(), 'errors.password.0');
        $this->assertStringContainsString('password confirmation does not match', $errorMessage);
    }

    public function testSuccessChange()
    {
        // create user
        $user = factory(User::class)->create([
            'password' => bcrypt('test_password')
        ]);

        // login
        $token = $this->login($user->email, 'test_password');

        // user tries change password for self
        $response = $this->json('PUT', "/users/{$user->id}/password", [
            'password_old'          => 'test_password',
            'password'              => 'super_secret',
            'password_confirmation' => 'super_secret',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200); // password successfully changed


        // perform logout
        $response = $this->delete('/logout', [], ['Authorization' => 'Bearer ' . $token]);
        $response->assertStatus(200);


        // try login via old password
        $response = $this->json('POST', '/login', [
            'email'    => $user->email,
            'password' => 'test_password'
        ]);
        $response->assertStatus(400); // invalid credentials


        // try login via new password
        $response = $this->json('POST', '/login', [
            'email'    => $user->email,
            'password' => 'super_secret'
        ]);
        $response->assertStatus(200); // OK
    }

    public function testChangePasswordForUserAsAdmin()
    {
        $user = factory(User::class)->create(['password' => bcrypt('password1')]);
        $admin = factory(User::class)->state('admin')->create();

        $token = $this->login($admin->email);

        // admin tries change user's password
        $response = $this->json('PUT', "/users/{$user->id}/password", [
            'password_old'          => 'password1',
            'password'              => 'password2',
            'password_confirmation' => 'password2'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(200);

        // check password changed, old 'password1', new 'password2'
        $dbUser = User::find($user->id);
        $this->assertTrue(Hash::check('password2', $dbUser->password));
    }
}
