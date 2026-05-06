<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalAppResource\Pages;
use App\Models\PortalApp;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PortalAppResource extends Resource
{
    protected static ?string $model = PortalApp::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Portal';

    protected static ?string $modelLabel = 'Aplikasi Portal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Aplikasi')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Aplikasi')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('url')
                        ->label('URL / Link Aplikasi')
                        ->required()
                        ->url()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('icon')
                        ->label('Icon Aplikasi')
                        ->searchable()
                        ->allowHtml()
                        ->options(self::getIconOptions())
                        ->placeholder('-- Pilih Icon --')
                        ->helperText('Icon ditampilkan di card jika logo tidak diunggah'),
                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo Aplikasi (Override icon)')
                        ->image()
                        ->directory('portal-apps')
                        ->visibility('public'),
                ])->columns(2),

                Section::make('Pengaturan')->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                    Forms\Components\TextInput::make('order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aplikasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Returns a curated list of heroicons for portal app icons.
     *
     * @return array<string, string>
     */
    public static function getIconOptions(): array
    {
        $icons = [
            'heroicon-o-chart-bar' => 'Chart Bar (Statistik)',
            'heroicon-o-document-text' => 'Document Text (Dokumen)',
            'heroicon-o-users' => 'Users (Pengguna)',
            'heroicon-o-academic-cap' => 'Academic Cap (Pendidikan/Magang)',
            'heroicon-o-envelope' => 'Envelope (Surat/Email)',
            'heroicon-o-briefcase' => 'Briefcase (Pekerjaan)',
            'heroicon-o-globe-alt' => 'Globe (Portal/Web)',
            'heroicon-o-building-office' => 'Building Office (Kantor)',
            'heroicon-o-chart-pie' => 'Chart Pie (Laporan)',
            'heroicon-o-clipboard-document-list' => 'Clipboard List (Daftar)',
            'heroicon-o-cog-6-tooth' => 'Cog (Pengaturan)',
            'heroicon-o-computer-desktop' => 'Computer (Aplikasi Desktop)',
            'heroicon-o-currency-dollar' => 'Currency (Keuangan)',
            'heroicon-o-finger-print' => 'Fingerprint (Identitas)',
            'heroicon-o-home' => 'Home (Beranda)',
            'heroicon-o-identification' => 'Identification (ID/KTP)',
            'heroicon-o-map' => 'Map (Peta)',
            'heroicon-o-presentation-chart-line' => 'Presentation (Presentasi)',
            'heroicon-o-server' => 'Server (Sistem/IT)',
            'heroicon-o-shield-check' => 'Shield Check (Keamanan)',
            'heroicon-o-table-cells' => 'Table (Data/Tabel)',
            'heroicon-o-truck' => 'Truck (Logistik)',
            'heroicon-o-wrench-screwdriver' => 'Wrench (Alat/Teknis)',
        ];

        $options = [];
        foreach ($icons as $value => $label) {
            $options[$value] = "<span style='display:flex;align-items:center;gap:8px'>
                <x-dynamic-component :component=\"'{$value}'\" style='width:16px;height:16px;display:inline-block;flex-shrink:0' />
                {$label}
            </span>";
        }

        return $options;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortalApps::route('/'),
            'create' => Pages\CreatePortalApp::route('/create'),
            'edit' => Pages\EditPortalApp::route('/{record}/edit'),
        ];
    }
}
