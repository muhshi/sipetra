<?php

namespace App\Filament\Resources\OAuthClients\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OAuthClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Client')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('grant_types')
                    ->label('Grant Type')
                    ->badge()
                    ->state(fn ($record): string => implode(', ', array_filter($record->grant_types ?? [], fn ($g) => $g !== 'refresh_token')))
                    ->color('info'),

                TextColumn::make('redirect_uris')
                    ->label('Redirect URIs')
                    ->state(fn ($record): string => implode(', ', $record->redirect_uris ?? []))
                    ->limit(50)
                    ->tooltip(fn ($record): string => implode("\n", $record->redirect_uris ?? []))
                    ->placeholder('— tidak ada —')
                    ->toggleable(),

                IconColumn::make('revoked')
                    ->label('Revoked')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('revoked')
                    ->label('Status Revoked'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
