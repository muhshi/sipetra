<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\IdentityType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->maxLength(255),
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->label('Roles'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('identity_type')
                                    ->label('Tipe Identitas')
                                    ->options(IdentityType::class)
                                    ->default(IdentityType::Admin)
                                    ->required()
                                    ->live(),
                                TextInput::make('phone')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->required()
                                    ->inline(false),
                            ]),
                    ]),

                Section::make('Profil Detil')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('nip')
                                    ->label('NIP/NIP Baru')
                                    ->visible(fn ($get) => $get('identity_type') === IdentityType::Pegawai->value),
                                TextInput::make('sobat_id')
                                    ->label('Sobat ID')
                                    ->visible(fn ($get) => $get('identity_type') === IdentityType::Mitra->value),
                                Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'L' => 'Laki-laki',
                                        'P' => 'Perempuan',
                                    ]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('jabatan')
                                    ->label('Jabatan'),
                                TextInput::make('unit_kerja')
                                    ->label('Unit Kerja'),
                                TextInput::make('kd_satker')
                                    ->label('Kode Satker'),
                                TextInput::make('golongan')
                                    ->label('Golongan'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tempat_lahir')
                                    ->label('Tempat Lahir'),
                                DatePicker::make('tanggal_lahir')
                                    ->label('Tanggal Lahir'),
                            ]),
                        TextInput::make('pendidikan')
                            ->label('Pendidikan'),
                        FileUpload::make('avatar_url')
                            ->label('Avatar')
                            ->image()
                            ->directory('avatars')
                            ->visibility('public'),
                    ])
                    ->collapsible(),
            ]);
    }
}
