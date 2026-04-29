<?php

namespace App\Filament\Resources\EmployeeProfiles\Tables;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.avatar_url')
                    ->label('Foto')
                    ->circular(),
                TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.nip')
                    ->label('NIP')
                    ->searchable(),
                TextColumn::make('status_pegawai')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('user.jabatan')
                    ->label('Jabatan')
                    ->limit(30),
                TextColumn::make('user.golongan')
                    ->label('Gol')
                    ->sortable(),
                TextColumn::make('mk_tahun')
                    ->label('Masa Kerja')
                    ->state(fn ($record) => "{$record->mk_tahun}th {$record->mk_bulan}bl"),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
