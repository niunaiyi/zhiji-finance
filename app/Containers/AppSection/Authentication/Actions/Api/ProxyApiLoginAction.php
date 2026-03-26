<?php

namespace App\Containers\AppSection\Authentication\Actions\Api;

use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Auth;
use App\Containers\AppSection\Authentication\Exceptions\LoginFailed;

final class ProxyApiLoginAction extends ParentAction
{
    public function run(array $credentials): array
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            throw LoginFailed::create();
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ];
    }
}
