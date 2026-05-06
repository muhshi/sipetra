<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalAppResource\Pages;
use App\Models\PortalApp;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortalAppResource extends Resource
{
    protected static ?string $model = PortalApp::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Manajemen Portal';
    
    protected static ?string $modelLabel = 'Aplikasi Portal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Informasi Aplikasi')->schema([
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
                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo Aplikasi')
                        ->image()
                        ->directory('portal-apps')
                        ->columnSpanFull(),
                ])->columns(2),
                
                \Filament\Schemas\Components\Section::make('Pengaturan')->schema([
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
