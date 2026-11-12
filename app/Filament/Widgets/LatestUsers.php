<?php

namespace App\Filament\Widgets;

use App\Enums\IdentityType;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestUsers extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'operator']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->latest()->limit(5))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('identity_type')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        IdentityType::Pegawai => 'success',
                        IdentityType::Mitra => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
