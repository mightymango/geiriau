<?php

namespace App\Services\Reminders;

use DateTimeInterface;
use Illuminate\Support\Facades\Log;
use Smronju\NativephpLocalNotifications\Facades\LocalNotifications;
use Throwable;

/**
 * On-device notifications via the free smronju/nativephp-local-notifications plugin.
 * Calls are wrapped so the app keeps working in a desktop browser, where there's no native bridge.
 */
class LocalNotificationsGateway implements NotificationGateway
{
    public function requestPermission(): void
    {
        $this->attempt(fn () => LocalNotifications::requestPermission());
    }

    public function schedule(string $id, string $title, string $body, DateTimeInterface $at): void
    {
        $this->attempt(fn () => LocalNotifications::schedule($id, $title, $body, $at));
    }

    public function cancel(string $id): void
    {
        $this->attempt(fn () => LocalNotifications::cancel($id));
    }

    private function attempt(callable $call): void
    {
        try {
            $call();
        } catch (Throwable $e) {
            Log::debug('Local notification call skipped: '.$e->getMessage());
        }
    }
}
