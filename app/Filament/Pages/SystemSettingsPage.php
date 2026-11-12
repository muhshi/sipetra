<?php

namespace App\Filament\Pages;

use App\Settings\SystemSettings;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class SystemSettingsPage extends SettingsPage
{
    protected static string $settings = SystemSettings::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan';
    }

    public function getTitle(): string|Htmlable
    {
        return 'System Settings';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
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
