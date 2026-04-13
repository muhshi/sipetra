<?php

namespace App\Filament\Pages;

use App\Settings\SystemSettings;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Pages\SettingsPage;

class SystemSettingsPage extends SettingsPage
{
    protected static string $settings = \App\Settings\SystemSettings::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'System Settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('session_lifetime')
                    ->label('Session Lifetime')
                    ->options([
                        60 => '60 Menit (1 Jam)',
                        120 => '120 Menit (2 Jam)',
                        1440 => '1 Hari (24 Jam)',
                        10080 => '7 Hari (1 Minggu)',
                        43200 => '30 Hari (1 Bulan)',
                        2628000 => 'Lifetime / Selamanya (5 Tahun)',
                    ])
                    ->required()
                    ->helperText('Durasi dalam menit sebelum session pengguna otomatis berakhir atau kedaluwarsa.'),
            ]);
    }
}
