<?php

namespace App\Filament\Resources\Passport\RelationManagers;

use App\Enums\AccessRuleType;
use App\Enums\IdentityType;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class AccessRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'accessRules';

    protected static ?string $title = 'Access Rules';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Select::make('rule_type')
                    ->label('Rule Type')
                    ->options(AccessRuleType::class)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('rule_value', null)),
                Select::make('rule_value')
                    ->label('Rule Value')
                    ->required()
                    ->searchable()
                    ->options(fn (Get $get): array => $this->getInitialRuleValueOptions($get('rule_type')))
                    ->getSearchResultsUsing(fn (Get $get, ?string $search): array => $this->searchRuleValueOptions($get('rule_type'), $search))
                    ->getOptionLabelUsing(fn (Get $get, $value): ?string => $this->getRuleValueLabel($get('rule_type'), $value))
                    ->visible(fn (Get $get): bool => $this->shouldShowRuleValueField($get('rule_type')))
                    ->dehydrated(fn (Get $get): bool => $this->shouldShowRuleValueField($get('rule_type'))),
                TextInput::make('rule_value_text')
                    ->label('Rule Value')
                    ->hidden(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rule_type')
                    ->label('Rule Type')
                    ->formatStateUsing(fn ($state): string => $state instanceof AccessRuleType ? $state->label() : (AccessRuleType::tryFrom((string) $state)?->label() ?? (string) $state)),
                TextColumn::make('rule_value')
                    ->label('Rule Value')
                    ->formatStateUsing(fn ($state, $record): string => $this->formatRuleValue($record->rule_type, (string) $state)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('rule_type')
                                ->label('Rule Type')
                                ->options(AccessRuleType::class)
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state, callable $set) => $set('rule_values', [])),
                            Select::make('rule_values')
                                ->label('Rule Values')
                                ->required()
                                ->multiple()
                                ->searchable()
                                ->options(fn (Get $get): array => $this->getInitialRuleValueOptions($get('rule_type')))
                                ->getSearchResultsUsing(fn (Get $get, ?string $search): array => $this->searchRuleValueOptions($get('rule_type'), $search))
                                ->getOptionLabelsUsing(fn (Get $get, array $values): array => $this->getRuleValueLabels($get('rule_type'), $values))
                                ->visible(fn (Get $get): bool => $this->shouldShowRuleValueField($get('rule_type'))),
                        ]),
                    ])
                    ->using(function (array $data): Model {
                        $ruleValues = collect($data['rule_values'] ?? [])
                            ->filter(fn ($value) => filled($value))
                            ->unique()
                            ->values();

                        if ($ruleValues->isEmpty()) {
                            throw new \InvalidArgumentException('Rule values wajib diisi.');
                        }

                        $firstRecord = null;

                        foreach ($ruleValues as $index => $ruleValue) {
                            $record = $this->getRelationship()->create([
                                'rule_type' => $data['rule_type'],
                                'rule_value' => (string) $ruleValue,
                            ]);

                            if ($index === 0) {
                                $firstRecord = $record;
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Access rules created')
                            ->body("{$ruleValues->count()} rule berhasil ditambahkan.")
                            ->send();

                        return $firstRecord;
                    })
                    ->successNotification(null),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['rule_value'] = $data['rule_value'] ?? null;

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function getInitialRuleValueOptions(string|AccessRuleType|null $ruleType): array
    {
        $ruleType = $this->normalizeRuleType($ruleType);

        return match ($ruleType) {
            AccessRuleType::User => [],
            AccessRuleType::SipetraRole => Role::query()
                ->orderBy('name')
                ->pluck('name', 'name')
                ->all(),
            AccessRuleType::IdentityType => collect(IdentityType::cases())
                ->mapWithKeys(fn (IdentityType $type): array => [$type->value => $type->label()])
                ->all(),
            default => [],
        };
    }

    private function searchRuleValueOptions(string|AccessRuleType|null $ruleType, ?string $search): array
    {
        $ruleType = $this->normalizeRuleType($ruleType);
        $search = trim((string) $search);

        return match ($ruleType) {
            AccessRuleType::User => $this->searchUsers($search),
            default => $this->getInitialRuleValueOptions($ruleType),
        };
    }

    private function searchUsers(string $search): array
    {
        return User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('nip_baru', 'like', "%{$search}%")
                        ->orWhere('sobat_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                (string) $user->getKey() => $this->formatUserLabel($user),
            ])
            ->all();
    }

    private function formatRuleValue(AccessRuleType|string|null $ruleType, string $ruleValue): string
    {
        $ruleType = $this->normalizeRuleType($ruleType);

        return match ($ruleType) {
            AccessRuleType::User => User::query()->find($ruleValue)?->name ?? $ruleValue,
            AccessRuleType::IdentityType => IdentityType::tryFrom($ruleValue)?->label() ?? $ruleValue,
            default => $ruleValue,
        };
    }

    private function getRuleValueLabel(string|AccessRuleType|null $ruleType, mixed $value): ?string
    {
        $labels = $this->getRuleValueLabels($ruleType, [$value]);

        return $labels[(string) $value] ?? null;
    }

    private function getRuleValueLabels(string|AccessRuleType|null $ruleType, array $values): array
    {
        $ruleType = $this->normalizeRuleType($ruleType);
        $values = collect($values)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (string) $value)
            ->values();

        if ($values->isEmpty()) {
            return [];
        }

        return match ($ruleType) {
            AccessRuleType::User => User::query()
                ->whereKey($values->all())
                ->get()
                ->mapWithKeys(fn (User $user): array => [
                    (string) $user->getKey() => $this->formatUserLabel($user),
                ])
                ->all(),
            default => collect($this->getInitialRuleValueOptions($ruleType))
                ->only($values->all())
                ->all(),
        };
    }

    private function formatUserLabel(User $user): string
    {
        $identifier = $user->nip_baru ?: ($user->nip ?: ($user->sobat_id ?: $user->email));

        return "{$user->name} ({$identifier})";
    }

    private function shouldShowRuleValueField(string|AccessRuleType|null $ruleType): bool
    {
        return $this->normalizeRuleType($ruleType) instanceof AccessRuleType;
    }

    private function normalizeRuleType(string|AccessRuleType|null $ruleType): ?AccessRuleType
    {
        return $ruleType instanceof AccessRuleType
            ? $ruleType
            : AccessRuleType::tryFrom((string) $ruleType);
    }
}
