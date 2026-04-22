<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport;

use App\Support\Passport\TokenDisplayNameResolver;
use Filament\Actions\Action;
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
use UnitEnum;

class TokenResource extends BaseTokenResource
{
    protected static ?string $slug = 'tokens';

    protected static string|UnitEnum|null $navigationGroup = 'SSO';
    protected static ?string $pluralModelLabel = 'Token';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('revoked', false)->count();
    }

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
            ->actions([
                Action::make('revoke')
                    ->label('Revoke')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->visible(fn($record, $livewire) => !$record->revoked && (method_exists($livewire, 'isReadOnly') ? !$livewire->isReadOnly() : true))
                    ->action(fn(Model $record) => $record->update(['revoked' => true])),
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
