<?php

namespace App\Containers\Finance\Auth\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->token()) {
            $user->token()->revoke();
        }

        return Response::json([
            'message' => 'Successfully logged out'
        ]);
    }
}
