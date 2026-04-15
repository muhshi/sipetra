<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport;

use App\Filament\Resources\Passport\Pages;
use App\Support\Passport\TokenDisplayNameResolver;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Passport;
use N3XT0R\FilamentPassportUi\Resources\BaseResource\Schemas\Columns\CreatedAtColumn;
use N3XT0R\FilamentPassportUi\Resources\BaseResource\Schemas\Columns\IdColumn;
use N3XT0R\FilamentPassportUi\Resources\BaseResource\Schemas\Columns\NameColumn;
use N3XT0R\FilamentPassportUi\Resources\TokenResource as BaseTokenResource;
use N3XT0R\FilamentPassportUi\Resources\TokenResource\Schemas\Components\Columns\ClientIdColumn;
use N3XT0R\FilamentPassportUi\Resources\TokenResource\Schemas\Components\Columns\RevokedColumn;
use N3XT0R\FilamentPassportUi\Resources\TokenResource\Schemas\Components\Columns\ScopesColumn;

class TokenResource extends BaseTokenResource
{
    protected static ?string $slug = 'tokens';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IdColumn::make()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('user_id')
                    ->label(__('filament-passport-ui::passport-ui.token_resource.column.user_name'))
                    ->formatStateUsing(function (Model $record): string {
                        return app(TokenDisplayNameResolver::class)->execute($record);
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return app(TokenDisplayNameResolver::class)->applySearch($query, $search);
                    })
                    ->toggleable(),
                ClientIdColumn::make(),
                NameColumn::make()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                ScopesColumn::make(),
                RevokedColumn::make(),
                CreatedAtColumn::make(),
                CreatedAtColumn::make('expires_at')
                    ->label(__('filament-passport-ui::passport-ui.common.expires_at')),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('client');
    }

    public static function getModel(): string
    {
        return Passport::tokenModel();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTokens::route('/'),
        ];
    }
}
