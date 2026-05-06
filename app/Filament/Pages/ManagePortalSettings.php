<?php

namespace App\Filament\Pages;

use App\Settings\PortalSettings;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManagePortalSettings extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = PortalSettings::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Portal';

    protected static ?string $title = 'Pengaturan Portal';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Hero Section')->schema([
                    Forms\Components\TextInput::make('hero_title')
                        ->label('Judul Utama')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('hero_accent_title')
                        ->label('Judul Aksen (teks berwarna di baris ke-2)')
                        ->maxLength(255)
                        ->helperText('Contoh: All in One Portal'),
                    Forms\Components\TextInput::make('hero_subtitle')
                        ->label('Sub Judul / Deskripsi')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\ColorPicker::make('accent_color')
                        ->label('Warna Aksen')
                        ->required(),
                    Forms\Components\FileUpload::make('background_image')
                        ->label('Gambar Latar Hero (Opsional)')
                        ->image()
                        ->directory('portal-settings')
                        ->visibility('public')
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
