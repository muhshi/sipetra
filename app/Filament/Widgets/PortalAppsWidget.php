<?php

namespace App\Filament\Widgets;

use App\Models\PortalApp;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PortalAppsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Daftar Aplikasi Portal';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'operator']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(PortalApp::query()->orderBy('order', 'asc'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Aplikasi')
                    ->weight('bold')
                    ->icon(fn ($record) => $record->icon),
                TextColumn::make('url')
                    ->label('URL')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->color('primary'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                TextColumn::make('order')
                    ->label('Urutan')
                    ->numeric(),
            ])
            ->paginated(false);
    }
}
