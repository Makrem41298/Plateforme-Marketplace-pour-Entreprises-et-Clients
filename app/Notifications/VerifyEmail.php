<?php

namespace App\Notifications;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class VerifyEmail extends Notification
{
    public function toMail($notifiable)
    {
        // Generate the backend verification URL with a signature
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Extract the query parameters (id, hash, expires, signature)
        $query = parse_url($verificationUrl, PHP_URL_QUERY);

        // Build the frontend URL with the same parameters
        $frontendUrl = env('FRONTEND_URL') . '/verify-email?' . $query;

        return (new MailMessage)
            ->subject('Verify Your Email')
            ->line('Click the button below to verify your email.')
            ->action('Verify Email', $frontendUrl);
    }
}
