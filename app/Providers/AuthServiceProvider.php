<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = config('app.frontend_url') . '/verify-email';
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());
            $guard = Auth::getDefaultDriver();

            $signedUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $id,
                    'hash' => $hash,
                    'guard' =>$guard,
                ]
            );

            // Parse query parameters from the signed URL
            $parsedUrl = parse_url($signedUrl);
            parse_str($parsedUrl['query'] ?? '', $queryParams);

            $frontendParams = [
                'guard' =>$guard,
                'id' => $id,
                'hash' => $hash,
                'expires' => $queryParams['expires'],
                'signature' => $queryParams['signature'],
            ];

            return $frontendUrl . '?' . http_build_query($frontendParams);
        });
    }
}
