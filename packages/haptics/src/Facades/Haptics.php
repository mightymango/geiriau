<?php

namespace Geiriau\Haptics\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void impact(string $style = 'light')
 * @method static void success()
 * @method static void warning()
 * @method static void error()
 * @method static void selection()
 *
 * @see \Geiriau\Haptics\Haptics
 */
class Haptics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Geiriau\Haptics\Haptics::class;
    }
}
