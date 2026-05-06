<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PortalAppsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Daftar Aplikasi Portal';

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\PortalApp::query()->orderBy('order', 'asc'))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aplikasi')
                    ->weight('bold')
                    ->icon(fn ($record) => $record->icon),
                \Filament\Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->color('primary'),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->numeric(),
            ])
            ->paginated(false);
    }
}
