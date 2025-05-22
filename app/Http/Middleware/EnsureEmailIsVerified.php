<?php

namespace App\Http\Middleware;

use App\Http\Controllers\apiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    use apiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('client')->check() && !auth('client')->user()->hasVerifiedEmail()) {

            return $this->apiResponse('Email not verified',null,403);
        }
        if (auth('entreprise')->check() && !auth('entreprise')->user()->hasVerifiedEmail()) {

            return $this->apiResponse('Email not verified',null,403);
        }

        return $next($request);    }
}
