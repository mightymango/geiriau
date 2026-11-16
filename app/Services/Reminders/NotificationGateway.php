<?php

namespace App\Services\Reminders;

use DateTimeInterface;

interface NotificationGateway
{
    public function requestPermission(): void;

    public function schedule(string $id, string $title, string $body, DateTimeInterface $at): void;

    public function cancel(string $id): void;
}
