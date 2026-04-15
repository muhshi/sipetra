<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\AccessRuleType;
use App\Enums\IdentityType;
use App\Models\ClientRole;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class AccessRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'accessRules';

    protected static ?string $title = 'Aturan Akses';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('rule_type')
                ->label('Tipe Aturan')
                ->options(AccessRuleType::class)
                ->required()
                ->live()
                ->afterStateUpdated(fn (callable $set) => $set('rule_value', null)),

            // Tampil jika rule_type = 'user'
            Select::make('rule_value')
                ->label('Pilih User')
                ->searchable()
                ->getSearchResultsUsing(fn (string $search) => User::where('is_active', true)
                    ->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->limit(20)
                    ->get()
                    ->mapWithKeys(fn (User $user) => [
                        $user->id => "{$user->name}".($user->nip ? " ({$user->nip})" : ''),
                    ]))
                ->getOptionLabelUsing(fn ($value) => User::find($value)?->name ?? $value)
                ->visible(fn (Get $get): bool => $get('rule_type') === AccessRuleType::User->value)
                ->required(fn (Get $get): bool => $get('rule_type') === AccessRuleType::User->value),

            // Tampil jika rule_type = 'sipetra_role'
            Select::make('rule_value')
                ->label('Pilih Role Sipetra')
                ->options(fn () => Role::pluck('name', 'name'))
                ->searchable()
                ->visible(fn (Get $get): bool => $get('rule_type') === AccessRuleType::SipetraRole->value)
                ->required(fn (Get $get): bool => $get('rule_type') === AccessRuleType::SipetraRole->value),

            // Tampil jika rule_type = 'identity_type'
            Select::make('rule_value')
                ->label('Tipe Identitas')
                ->options(IdentityType::class)
                ->visible(fn (Get $get): bool => $get('rule_type') === AccessRuleType::IdentityType->value)
                ->required(fn (Get $get): bool => $get('rule_type') === AccessRuleType::IdentityType->value),

            Select::make('client_role_id')
                ->label('Role di Aplikasi (Opsional)')
                ->helperText('Jika cocok dengan rule ini, user akan mendapat role berikut di aplikasi klien.')
                ->options(fn () => ClientRole::where('client_id', $this->getOwnerRecord()->id)
                    ->pluck('name', 'id'))
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rule_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (AccessRuleType $state): string => $state->label())
                    ->color(fn (AccessRuleType $state): string => match ($state) {
                        AccessRuleType::User => 'info',
                        AccessRuleType::SipetraRole => 'warning',
                        AccessRuleType::IdentityType => 'success',
                    }),

                TextColumn::make('rule_value')
                    ->label('Nilai')
                    ->formatStateUsing(function (string $state, $record): string {
                        if ($record->rule_type === AccessRuleType::User) {
                            return User::find($state)?->name ?? $state;
                        }
                        if ($record->rule_type === AccessRuleType::IdentityType) {
                            return IdentityType::tryFrom($state)?->label() ?? $state;
                        }

                        return $state;
                    })
                    ->searchable(),

                TextColumn::make('role.name')
                    ->label('Role Client')
                    ->badge()
                    ->color('primary')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah Rule'),
            ]);
    }
}
