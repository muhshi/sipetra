<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddHeroAccentTitleToPortalSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('portal.hero_accent_title', 'All in One Portal');
    }
}
