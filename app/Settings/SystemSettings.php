<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{
    public int $session_lifetime;

    public static function group(): string
    {
        return 'system';
    }
}
