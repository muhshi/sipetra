<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\IdentityType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=FFFFFF&background=030712'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('identity_type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('sobat_id')
                    ->label('Sobat ID')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('kd_satker')
                    ->label('Satker')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('identity_type')
                    ->label('Tipe Identitas')
                    ->options(IdentityType::class),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
