<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas;

use App\Enums\AccessRuleType;
use App\Enums\IdentityType;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class SimplifiedClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Aplikasi SSO')
                ->description('Identitas dasar aplikasi web klien yang akan diintegrasikan dengan SIPETRA.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Aplikasi')
                        ->placeholder('Contoh: Alfath, E-Rapot, Magang')
                        ->required()
                        ->unique('oauth_clients', 'name', ignoreRecord: true)
                        ->maxLength(255),

                    Grid::make(2)->schema([
                        TextInput::make('app_url')
                            ->label('URL / Domain Aplikasi')
                            ->placeholder('https://alfath.bpsdemak.id')
                            ->url()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                if (filled($state) && blank($get('redirect_uri'))) {
                                    $set('redirect_uri', rtrim($state, '/').'/auth/callback');
                                }
                            }),

                        TextInput::make('redirect_uri')
                            ->label('Callback / Redirect URI')
                            ->placeholder('https://alfath.bpsdemak.id/auth/callback')
                            ->helperText('Otomatis terisi dari URL aplikasi. Ubah jika callback Anda berbeda.')
                            ->url()
                            ->required(),
                    ]),
                ]),

            Section::make('Kebijakan Akses (Siapa yang Boleh Login?)')
                ->description('Tentukan batasan pengguna SIPETRA yang diizinkan untuk masuk ke aplikasi ini.')
                ->schema([
                    Radio::make('access_preset')
                        ->label('Pilihan Akses')
                        ->options([
                            'open' => '🟢 Semua Pengguna (Open) — Pegawai & Mitra aktif dapat login',
                            'pegawai' => '🔵 Khusus Pegawai BPS — Hanya PNS & PPPK aktif',
                            'mitra' => '🟠 Khusus Mitra Statistik — Hanya mitra terdaftar yang aktif',
                            'custom' => '⚙️ Kustom / Terbatas — Tentukan daftar user, role, atau identitas tertentu',
                        ])
                        ->default('open')
                        ->live()
                        ->required(),

                    Repeater::make('access_rules')
                        ->label('Aturan Akses Kustom')
                        ->helperText('Tambahkan kombinasi user, role, atau tipe identitas yang diizinkan.')
                        ->visible(fn (Get $get): bool => $get('access_preset') === 'custom')
                        ->schema([
                            Select::make('rule_type')
                                ->label('Tipe Aturan')
                                ->options(AccessRuleType::class)
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state, Set $set) => $set('rule_values', [])),

                            Select::make('rule_values')
                                ->label('Nilai Aturan')
                                ->required()
                                ->multiple()
                                ->searchable()
                                ->options(fn (Get $get): array => self::getRuleOptions($get('rule_type')))
                                ->getSearchResultsUsing(fn (Get $get, ?string $search): array => self::searchRuleOptions($get('rule_type'), $search))
                                ->getOptionLabelsUsing(fn (Get $get, array $values): array => self::getRuleLabels($get('rule_type'), $values)),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Integrasi Portal Landing Page')
                ->description('Sinkronisasikan pembuatan SSO client ini langsung ke halaman depan portal SIPETRA.')
                ->schema([
                    Toggle::make('add_to_portal')
                        ->label('Tampilkan aplikasi ini di Portal Landing Page SIPETRA?')
                        ->default(false)
                        ->live(),

                    Grid::make(2)
                        ->visible(fn (Get $get): bool => (bool) $get('add_to_portal'))
                        ->schema([
                            TextInput::make('portal_full_name')
                                ->label('Kepanjangan Nama Aplikasi (Opsional)')
                                ->placeholder('Contoh: Aplikasi Layanan Fasilitasi Terpadu'),

                            TextInput::make('portal_order')
                                ->label('Urutan Tampil')
                                ->numeric()
                                ->default(0),

                            Textarea::make('portal_description')
                                ->label('Deskripsi Singkat')
                                ->placeholder('Deskripsi singkat yang tampil pada kartu di landing page.')
                                ->columnSpanFull(),

                            FileUpload::make('portal_logo')
                                ->label('Logo Aplikasi')
                                ->image()
                                ->directory('portal-apps')
                                ->visibility('public')
                                ->helperText('Rekomendasi format PNG/WebP transparan.'),

                            TextInput::make('portal_icon')
                                ->label('Icon Heroicon')
                                ->placeholder('heroicon-o-squares-2x2')
                                ->default('heroicon-o-squares-2x2')
                                ->helperText('Digunakan jika logo aplikasi tidak diunggah.'),
                        ]),
                ])
                ->collapsible(),

            Section::make('Pengaturan Lanjutan (OAuth Scopes & Grant)')
                ->description('Konfigurasi teknis bawaan OAuth2. Secara default sudah disiapkan dengan standar terbaik.')
                ->collapsed()
                ->collapsible()
                ->schema([
                    CheckboxList::make('scopes')
                        ->label('OAuth Scopes yang Diizinkan')
                        ->options([
                            'profile:read' => 'profile:read (Nama, email, avatar)',
                            'identity_pegawai:read' => 'identity_pegawai:read (NIP Baru/Lama)',
                            'identity_mitra:read' => 'identity_mitra:read (SOBAT ID, NIK)',
                            'employee:read' => 'employee:read (Jabatan, Golongan, Masa Kerja, Agama)',
                            'contact:read' => 'contact:read (Nomor HP, Alamat)',
                            'roles:read' => 'roles:read (Hak akses & role sistem)',
                        ])
                        ->default([
                            'profile:read',
                            'identity_pegawai:read',
                            'identity_mitra:read',
                            'employee:read',
                            'contact:read',
                            'roles:read',
                        ])
                        ->columns(2),
                ]),
        ]);
    }

    private static function getRuleOptions(string|AccessRuleType|null $ruleType): array
    {
        $type = $ruleType instanceof AccessRuleType ? $ruleType : AccessRuleType::tryFrom((string) $ruleType);

        return match ($type) {
            AccessRuleType::SipetraRole => Role::query()->orderBy('name')->pluck('name', 'name')->all(),
            AccessRuleType::IdentityType => collect(IdentityType::cases())
                ->mapWithKeys(fn (IdentityType $item): array => [$item->value => $item->label()])
                ->all(),
            default => [],
        };
    }

    private static function searchRuleOptions(string|AccessRuleType|null $ruleType, ?string $search): array
    {
        $type = $ruleType instanceof AccessRuleType ? $ruleType : AccessRuleType::tryFrom((string) $ruleType);
        $search = trim((string) $search);

        if ($type === AccessRuleType::User) {
            return User::query()
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('nip_baru', 'like', "%{$search}%")
                        ->orWhere('sobat_id', 'like', "%{$search}%");
                })
                ->limit(30)
                ->get()
                ->mapWithKeys(fn (User $user): array => [
                    (string) $user->getKey() => "{$user->name} (".($user->nip_baru ?: ($user->sobat_id ?: $user->email)).')',
                ])
                ->all();
        }

        return self::getRuleOptions($ruleType);
    }

    private static function getRuleLabels(string|AccessRuleType|null $ruleType, array $values): array
    {
        $type = $ruleType instanceof AccessRuleType ? $ruleType : AccessRuleType::tryFrom((string) $ruleType);
        $values = collect($values)->filter(fn ($v) => filled($v))->map(fn ($v) => (string) $v)->values();

        if ($values->isEmpty()) {
            return [];
        }

        if ($type === AccessRuleType::User) {
            return User::query()
                ->whereKey($values->all())
                ->get()
                ->mapWithKeys(fn (User $user): array => [
                    (string) $user->getKey() => "{$user->name} (".($user->nip_baru ?: ($user->sobat_id ?: $user->email)).')',
                ])
                ->all();
        }

        return collect(self::getRuleOptions($ruleType))->only($values->all())->all();
    }
}
