<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SsoAuditLogResource\Pages;
use App\Models\SsoAuditLog;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SsoAuditLogResource extends Resource
{
    protected static ?string $model = SsoAuditLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static string|\UnitEnum|null $navigationGroup = 'SSO';

    protected static ?string $modelLabel = 'Audit Log SSO';

    protected static ?string $pluralModelLabel = 'Audit Log SSO';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('client_id')
                    ->label('Aplikasi / Client')
                    ->searchable()
                    ->placeholder('SIPETRA System'),
                TextColumn::make('event')
                    ->label('Aktivitas')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'login_success' => 'Login Berhasil',
                        'login_denied' => 'Akses Ditolak',
                        'logout' => 'Logout',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'login_success' => 'success',
                        'login_denied' => 'danger',
                        'logout' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable()
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(50)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState())
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Aktivitas')
                    ->options([
                        'login_success' => 'Login Berhasil',
                        'login_denied' => 'Akses Ditolak',
                        'logout' => 'Logout',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSsoAuditLogs::route('/'),
        ];
    }
}
