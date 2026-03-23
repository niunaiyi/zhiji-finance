<?php

namespace Tests\Unit\Ship\Middleware;

use App\Ship\Middleware\SwitchTenantMiddleware;
use App\Ship\Tests\ShipTestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SwitchTenantMiddlewareTest extends ShipTestCase
{
    public function testMiddlewareRejectsRequestWithoutCompanyIdHeader(): void
    {
        // Arrange
        $middleware = new SwitchTenantMiddleware();
        $request = Request::create('/api/v1/accounts', 'GET');
        $user = $this->getTestingUser();
        $request->setUserResolver(fn() => $user);

        // Expect exception
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('X-Company-Id header is required');

        // Act
        $middleware->handle($request, function () {
            return new Response();
        });
    }

    public function testMiddlewareRejectsUnauthorizedCompanyAccess(): void
    {
        // Arrange
        $middleware = new SwitchTenantMiddleware();
        $request = Request::create('/api/v1/accounts', 'GET');
        $request->headers->set('X-Company-Id', '999');
        $user = $this->getTestingUser();
        $request->setUserResolver(fn() => $user);

        // Mock user_company_roles check to return null (no access)
        DB::shouldReceive('table')
            ->once()
            ->with('user_company_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->once()
            ->with('user_id', $user->id)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->once()
            ->with('company_id', '999')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->once()
            ->with('is_active', true)
            ->andReturnSelf();
        DB::shouldReceive('first')
            ->once()
            ->andReturn(null);

        // Expect exception
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied to company');

        // Act
        $middleware->handle($request, function () {
            return new Response();
        });
    }

    public function testMiddlewareBindsCompanyIdToContainer(): void
    {
        // Arrange
        $middleware = new SwitchTenantMiddleware();
        $request = Request::create('/api/v1/accounts', 'GET');
        $request->headers->set('X-Company-Id', '1');
        $user = $this->getTestingUser();
        $request->setUserResolver(fn() => $user);

        // Mock user_company_roles check
        DB::shouldReceive('table')
            ->once()
            ->with('user_company_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->once()
            ->with('user_id', $user->id)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->once()
            ->with('company_id', '1')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->once()
            ->with('is_active', true)
            ->andReturnSelf();
        DB::shouldReceive('first')
            ->once()
            ->andReturn((object)['role' => 'admin']);

        // Act
        $middleware->handle($request, function () {
            return new Response();
        });

        // Assert
        $this->assertEquals(1, app('current.company_id'));
        $this->assertEquals('admin', app('current.role'));
    }
}
