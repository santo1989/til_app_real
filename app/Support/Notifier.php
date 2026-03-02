<?php

namespace App\Support;

use Illuminate\Notifications\Notification;

class Notifier
{
    public static function send($notifiable, Notification $notification): void
    {
        if (!$notifiable) {
            return;
        }

        try {
            $notifiable->notify($notification);
        } catch (\Throwable $e) {
            // Swallow notification failures to avoid breaking workflows
        }
    }
}
