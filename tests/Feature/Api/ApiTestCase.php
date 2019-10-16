<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

abstract class ApiTestCase extends TestCase {

  public function get($uri, array $headers = []) {
    $api_prefix = config('api.endpoint_prefix');
    $uri = $api_prefix . $uri;
    return parent::get($uri, $headers);
  }

  public function post($uri, array $data = [], array $headers = []) {
    $api_prefix = config('api.endpoint_prefix');
    $uri = $api_prefix . $uri;
    return parent::post($uri, $data, $headers);
  }

  public function delete($uri, array $data = [], array $headers = []) {
    $api_prefix = config('api.endpoint_prefix');
    $uri = $api_prefix . $uri;
    return parent::delete($uri, $data, $headers);
  }

  public function json($method, $uri, array $data = [], array $headers = []) {
    $api_prefix = config('api.endpoint_prefix');
    $uri = $api_prefix . $uri;
    return parent::json($method, $uri, $data, $headers);
  }

  /**
   * @param string $email
   * @param string $password , default 'password' - is from users factory
   * @return string|null
   */
  protected function login(string $email, string $password = 'password'): ?string {
    $response = $this->json('POST', '/login', compact('email', 'password'));
    return $response->json('token');
  }
}