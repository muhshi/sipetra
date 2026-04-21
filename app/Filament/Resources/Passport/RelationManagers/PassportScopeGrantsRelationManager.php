<?php

namespace App\Filament\Resources\Passport\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeAction;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeGrant;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeResource;

class PassportScopeGrantsRelationManager extends RelationManager
{
    protected static string $relationship = 'passportScopeGrants';

    protected static ?string $title = 'Scope Grants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('resource_id')
                    ->label('Resource')
                    ->options(PassportScopeResource::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('action_id')
                    ->label('Action')
                    ->options(PassportScopeAction::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Hidden::make('context_client_id')
                    ->default(fn () => $this->getOwnerRecord()->id)
                    ->dehydrated(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('resource.name')
                    ->label('Resource')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action.name')
                    ->label('Action')
                    ->badge()
                    ->color('success')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Grant Scope')
                    ->using(function (array $data, string $model): PassportScopeGrant {
                        $record = $this->getOwnerRecord();

                        return PassportScopeGrant::create([
                            'resource_id' => $data['resource_id'],
                            'action_id' => $data['action_id'],
                            'context_client_id' => $record->id,
                            'tokenable_id' => $record->id,
                            'tokenable_type' => $record->getMorphClass(),
                        ]);
                    }),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label('Revoke')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->visible(fn ($livewire) => ! $livewire->isReadOnly())
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
