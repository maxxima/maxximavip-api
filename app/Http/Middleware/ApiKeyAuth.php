<?php

namespace App\Http\Middleware;

use App\Constants\EnvironmentKeys;
use App\Constants\HttpHeaderKeys;
use Closure;
use Illuminate\Support\Facades\Config;

class ApiKeyAuth
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * @OA\SecurityScheme(
     *      securityScheme="apiKeyAuth",
     *      in="header",
     *      name="x-api-key",
     *      type="apiKey",
     * ),
     */
    public function handle($request, Closure $next)
    {
        $disableApiAuth = env(EnvironmentKeys::APP_DISABLE_API_AUTH);
        if($disableApiAuth != 'true'){
            $clientApiKey = env(EnvironmentKeys::APP_API_CLIENT_API_KEY);
            $requestApiKey = $request->header(HttpHeaderKeys::X_API_KEY);

            if($requestApiKey != $clientApiKey){
                return response('Unauthorized.', 401);
            }
        }
        return $next($request);
    }

}
