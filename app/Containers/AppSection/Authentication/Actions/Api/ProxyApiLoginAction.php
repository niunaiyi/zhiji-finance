<?php

namespace App\Containers\AppSection\Authentication\Actions\Api;

use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Auth;
use App\Containers\AppSection\Authentication\Exceptions\LoginFailed;

final class ProxyApiLoginAction extends ParentAction
{
    /**
     * @param array<string, mixed> $credentials
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    public function run(array $credentials): array
    {
        $guard = Auth::guard('api');

        $token = $guard->attempt($credentials);

        if (!is_string($token)) {
            throw LoginFailed::create();
        }

        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) ($guard->factory()->getTTL() * 60),
        ];
    }
}
