<?php

namespace App\Http\Middleware;

use App\Http\Controllers\apiResponse;
use Closure;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    use apiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {

        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            auth()->shouldUse($guard); // Set the specified guard
            Log::info('Guard being used:', ['guard' => auth()->getDefaultDriver()]);

            try {
                $user = auth($guard)->setToken(JWTAuth::getToken())->authenticate();
                Log::info('Authenticated user:', ['user' => $user]);
                log::info('Authenticated user:', ['user' => $user]);


                return $next($request);
            } catch (Exception $e) {
                continue;
            }
        }

        return $this->apiResponse('Authorization Token not found',null,401);

    }
}
