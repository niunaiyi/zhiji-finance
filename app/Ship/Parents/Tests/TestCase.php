<?php

namespace App\Ship\Parents\Tests;

use Apiato\Core\Testing\TestCase as AbstractTestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends AbstractTestCase
{
    use LazilyRefreshDatabase;

    /**
     * Call the given URI with a JSON request.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @param  int  $options
     * @return \Illuminate\Testing\TestResponse
     */
    public function json($method, $uri, array $data = [], array $headers = [], $options = 0)
    {
        $headers['App-Identifier'] = $headers['App-Identifier'] ?? config('apiato.defaults.app');

        return parent::json($method, $uri, $data, $headers, $options);
    }

    /**
     * Set the currently logged in user for the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $guard
     * @return $this
     */
    public function actingAs(UserContract $user, $guard = null)
    {
        $guard = $guard ?: 'api';

        if ($guard === 'api') {
            $token = JWTAuth::fromUser($user);
            $this->withHeader('Authorization', 'Bearer ' . $token);
        }

        $this->withHeader('App-Identifier', config('apiato.defaults.app'));

        return parent::actingAs($user, $guard);
    }
}
