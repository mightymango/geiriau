<?php

namespace Geiriau\Haptics;

class Haptics
{
    public function impact(string $style = 'light'): void
    {
        $this->call('GeiriauHaptics.Impact', ['style' => $style]);
    }

    public function success(): void
    {
        $this->call('GeiriauHaptics.Notify', ['type' => 'success']);
    }

    public function warning(): void
    {
        $this->call('GeiriauHaptics.Notify', ['type' => 'warning']);
    }

    public function error(): void
    {
        $this->call('GeiriauHaptics.Notify', ['type' => 'error']);
    }

    public function selection(): void
    {
        $this->call('GeiriauHaptics.Selection');
    }

    /** @param array<string, string> $parameters */
    private function call(string $function, array $parameters = []): void
    {
        if (! function_exists('nativephp_call')) {
            return;
        }

        nativephp_call($function, json_encode((object) $parameters));
    }
}
