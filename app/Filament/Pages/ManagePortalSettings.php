<?php

namespace App\Filament\Pages;

use App\Settings\PortalSettings;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Pages\SettingsPage;

class ManagePortalSettings extends SettingsPage
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = PortalSettings::class;
    
    protected static string | \UnitEnum | null $navigationGroup = 'Manajemen Portal';
    
    protected static ?string $title = 'Pengaturan Portal';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Hero Section')->schema([
                    Forms\Components\TextInput::make('hero_title')
                        ->label('Judul Utama (Hero Title)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('hero_subtitle')
                        ->label('Sub Judul (Hero Subtitle)')
                        ->maxLength(255),
                    Forms\Components\ColorPicker::make('accent_color')
                        ->label('Warna Aksen (Accent Color)')
                        ->required(),
                    Forms\Components\FileUpload::make('background_image')
                        ->label('Gambar Latar (Opsional)')
                        ->image()
                        ->directory('portal-settings'),
                ])->columns(2),
            ]);
    }
}
