<?php

namespace App\Services\Reminders;

use DateTimeInterface;
use Illuminate\Support\Facades\Log;

/** Used in the browser / when the plugin isn't installed: writes what would be scheduled to the log. */
class LogGateway implements NotificationGateway
{
    public function requestPermission(): void
    {
        Log::info('[reminders] permission requested');
    }

    public function schedule(string $id, string $title, string $body, DateTimeInterface $at): void
    {
        Log::info("[reminders] {$id} at {$at->format('D j M H:i T')}: {$title} — {$body}");
    }

    public function cancel(string $id): void
    {
        Log::info("[reminders] cancel {$id}");
    }
}
