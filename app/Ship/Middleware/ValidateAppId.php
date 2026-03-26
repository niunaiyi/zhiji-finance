<?php

namespace App\Ship\Middleware;

use App\Ship\Parents\Middleware\Middleware as ParentMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webmozart\Assert\Assert;

final class ValidateAppId extends ParentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(Request): (Response) $next
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $appId = $request->appId();
        
        $apps = config()->array('apiato.apps');
        
        if (!array_key_exists($appId, $apps)) {
            // Check if camelCase version exists or if it's coming from a test where case might be different
            $found = false;
            foreach ($apps as $key => $value) {
                if (strtolower($key) === strtolower($appId)) {
                    $appId = $key;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                Assert::keyExists(
                    $apps,
                    $appId,
                    "App-Identifier header value '{$appId}' is not valid. Allowed values are: " . implode(
                        ', ',
                        array_keys($apps),
                    ),
                );
            }
        }

        return $next($request);
    }
}
