<?php

namespace App\Containers\AppSection\Authentication\Tests\Functional\API;

use App\Ship\Parents\Tests\TestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Hash;

final class JWTAuthE2ETest extends TestCase
{
    protected $endpoint = '/v1/login';

    public function testLoginAndAccessSecureEndpoint(): void
    {
        $this->withoutExceptionHandling();
        // 1. Prepare User
        $user = User::factory()->create([
            'email' => 'e2e@test.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Perform Login
        $response = $this->postJson('/v1/login', [
            'email' => 'e2e@test.com',
            'password' => 'password',
        ], [
            'App-Identifier' => config('apiato.defaults.app')
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
        
        $token = $response->json('access_token');

        // 3. Access Secure Endpoint (e.g., Get Own Profile)
        $response = $this->getJson('/v1/profile', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => 'e2e@test.com']);

        // 4. Test Token Refresh
        $response = $this->postJson('/v1/refresh', [], [
            'Authorization' => 'Bearer ' . $token,
            'App-Identifier' => config('apiato.defaults.app')
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
        $newToken = $response->json('access_token');
        $this->assertNotEquals($token, $newToken);

        // 5. Test Logout
        $response = $this->postJson('/v1/logout', [], [
            'Authorization' => 'Bearer ' . $newToken,
            'App-Identifier' => config('apiato.defaults.app')
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Successfully logged out']);
    }

    public function testInvalidCredentialsReturnsError(): void
    {
        $response = $this->postJson('/v1/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'wrong',
        ], [
            'App-Identifier' => config('apiato.defaults.app')
        ]);

        $response->assertStatus(422); // LoginFailed exception returns 422
    }
}
