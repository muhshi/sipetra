<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreatePortalSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('portal.hero_title', 'Aplikasi Sipetra');
        $this->migrator->add('portal.hero_subtitle', 'Pilih aplikasi yang akan Anda gunakan');
        $this->migrator->add('portal.accent_color', '#06b6d4'); // Default cyan-500
        $this->migrator->add('portal.background_image', null);
    }
}
