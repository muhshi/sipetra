<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientRolesRelationManager extends RelationManager
{
    protected static string $relationship = 'clientRoles';

    protected static ?string $title = 'Daftar Role Aplikasi';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Role')
                ->placeholder('contoh: admin, guru, operator')
                ->required()
                ->maxLength(100),

            Textarea::make('description')
                ->label('Deskripsi')
                ->placeholder('Jelaskan hak akses role ini di aplikasi klien')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Role')
                    ->badge()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('userAccesses_count')
                    ->label('Jumlah User')
                    ->counts('userAccesses'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah Role'),
            ]);
    }
}
