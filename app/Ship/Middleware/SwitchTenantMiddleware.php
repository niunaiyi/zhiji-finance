<?php

namespace App\Ship\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SwitchTenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->header('X-Company-Id');

        if (!$companyId) {
            throw new HttpException(400, 'X-Company-Id header is required');
        }

        $user = $request->user();

        if (!$user) {
            throw new HttpException(401, 'Unauthenticated');
        }

        $userCompanyRole = DB::table('user_company_roles')
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if (!$userCompanyRole) {
            throw new HttpException(403, 'Access denied to company');
        }

        app()->instance('current.company_id', (int)$companyId);
        app()->instance('current.role', $userCompanyRole->role);

        return $next($request);
    }
}
