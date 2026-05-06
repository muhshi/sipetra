<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PortalSettings extends Settings
{
    public string $hero_title;
    public ?string $hero_subtitle;
    public string $accent_color;
    public ?string $background_image;

    public static function group(): string
    {
        return 'portal';
    }
}
