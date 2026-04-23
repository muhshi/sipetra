<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas;

use App\Enums\AccessRuleType;
use App\Enums\ClientAccessPolicy;
use App\Enums\IdentityType;
use App\Filament\Passport\Schemas\Fields\RedirectInput;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use N3XT0R\FilamentPassportUi\Application\StateResolvers\GrantType\NeedsUserPermissionState;
use N3XT0R\FilamentPassportUi\Application\StateResolvers\Token\GetOwnerState;
use N3XT0R\FilamentPassportUi\Resources\BaseResource\Components\ScopeCheckboxList;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\ClientWizardForm;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\GrantTypeSelect;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\NameInput;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Schemas\Fields\OwnerSelect;
use N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client;
use Spatie\Permission\Models\Role;

class ExtendedClientWizardForm extends ClientWizardForm
{
    public function getComponents(?Client $client = null): array
    {
        $dbScopesRequired = $this->configRepository->isUsingDatabaseScopes();
        $steps = [
            Wizard\Step::make('client')
                ->label('Informasi Klien')
                ->icon(Heroicon::OutlinedKey)
                ->description('Detail nama, tipe grant, dan redirect URI.')
                ->schema($this->getClientComponents($client)),
        ];

        if ($dbScopesRequired) {
            $steps[] = Wizard\Step::make('user_permission')
                ->visible(fn (Get $get) => app(NeedsUserPermissionState::class)->execute($get('grant_type')))
                ->label('Izin User')
                ->icon(Heroicon::OutlinedUser)
                ->description('Scope yang diizinkan untuk diberikan oleh user.')
                ->schema($this->getUserPermissionComponents($client));
        }

        $steps[] = Wizard\Step::make('access_configuration')
            ->label('Konfigurasi Akses')
            ->icon(Heroicon::OutlinedShieldCheck)
            ->description('Tentukan siapa saja yang boleh mengakses klien ini.')
            ->schema($this->getAccessConfigurationComponents());

        return [
            Wizard::make()
                ->steps($steps)
                ->columnSpanFull()
                ->persistStepInQueryString(),
        ];
    }

    public function getAccessConfigurationComponents(): array
    {
        return [
            Select::make('access_policy')
                ->label('Access Policy')
                ->options(collect(ClientAccessPolicy::cases())->mapWithKeys(
                    fn (ClientAccessPolicy $policy): array => [$policy->value => $policy->label()]
                )->all())
                ->default(ClientAccessPolicy::Restricted->value)
                ->live()
                ->required(),

            Repeater::make('access_rules')
                ->label('Access Rules')
                ->helperText('Hanya berlaku jika Policy adalah Restricted. Tambahkan aturan akses (User ID, Role, atau Tipe Identitas).')
                ->visible(fn (Get $get) => $get('access_policy') === ClientAccessPolicy::Restricted->value)
                ->schema([
                    Select::make('rule_type')
                        ->label('Tipe Aturan')
                        ->options(AccessRuleType::class)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('rule_values', [])),
                    Select::make('rule_values')
                        ->label('Nilai Aturan')
                        ->required()
                        ->multiple()
                        ->searchable()
                        ->searchPrompt('Ketik untuk mencari...')
                        ->loadingMessage('Sedang mencari...')
                        ->noSearchResultsMessage('Tidak ditemukan.')
                        ->options(fn (Get $get): array => $this->getInitialRuleValueOptions($get('rule_type')))
                        ->getSearchResultsUsing(fn (Get $get, ?string $search): array => $this->searchRuleValueOptions($get('rule_type'), $search))
                        ->getOptionLabelsUsing(fn (Get $get, array $values): array => $this->getRuleValueLabels($get('rule_type'), $values))
                        ->visible(fn (Get $get): bool => $this->shouldShowRuleValueField($get('rule_type')))
                        ->dehydrated(fn (Get $get): bool => $this->shouldShowRuleValueField($get('rule_type'))),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
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

    protected function getUserPermissionComponents(?Client $client = null): array
    {
        return [
            OwnerSelect::make()
                ->live()
                ->default(function (Set $set, Get $get) {
                    $set('owner_select', $get('owner'));
                })
                ->disabled()
                ->dehydrated(false),
            Grid::make()
                ->live()
                ->schema(fn (Get $get) => [
                    ScopeCheckboxList::make(
                        context: 'user',
                        name: 'user_scopes',
                        record: $this->resolveOwner($client, $get),
                        statePath: 'user_scopes',
                        contextClient: $this->resolveClient($client, $get),
                        allowed: collect($get('client_scopes') ?? [])
                            ->flatten()
                            ->filter()
                            ->values(),
                    ),
                ])
                ->key('user_scopes')
                ->columnSpanFull(),
        ];
    }

    protected function resolveOwner(?Client $client, Get $get): ?Model
    {
        if ($ownerId = $get('owner')) {
            return app(GetOwnerState::class)->execute($ownerId);
        }

        if ($ownerId = $get('owner_select')) {
            return app(GetOwnerState::class)->execute($ownerId);
        }

        if ($userId = $client?->getAttribute('owner_id')) {
            return app(GetOwnerState::class)->execute($userId);
        }

        return null;
    }

    public function getClientComponents(?Client $client = null): array
    {
        $dbScopesRequired = $this->configRepository->isUsingDatabaseScopes();

        $components = [
            NameInput::make()
                ->unique('oauth_clients', 'name', ignoreRecord: true),
            OwnerSelect::make()
                ->required(function (Get $get): bool {
                    $grantType = $get('grant_type');
                    if ($grantType === null) {
                        return false;
                    }

                    return app(NeedsUserPermissionState::class)->execute($grantType);
                }),
            RedirectInput::make(),
        ];

        if ($dbScopesRequired) {
            $components[] = Grid::make()
                ->schema(fn (Get $get) => [
                    ScopeCheckboxList::make(
                        context: 'client',
                        name: 'client_scopes',
                        record: $this->resolveClient($client, $get),
                        statePath: 'client_scopes',
                        contextClient: $this->resolveClient($client, $get),
                    ),
                ])
                ->key('client_scopes')
                ->columnSpanFull();
        }

        return [
            GrantTypeSelect::make('grant_type')
                ->live(),
            Grid::make()
                ->schema($components),
        ];
    }

    private function resolveClient(?Client $client, Get $get): ?Client
    {
        if ($id = $get('id')) {
            return Client::find($id);
        }

        return $client;
    }
}
