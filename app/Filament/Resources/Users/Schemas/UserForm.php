<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\IdentityType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->columnSpanFull(),
                    ]),

                Section::make('Identitas')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        Select::make('identity_type')
                            ->label('Tipe Identitas')
                            ->options(IdentityType::class)
                            ->default(IdentityType::Admin)
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('nip')
                            ->label('NIP Lama')
                            ->visible(fn (Get $get): bool => $get('identity_type') === IdentityType::Pegawai->value)
                            ->unique(ignoreRecord: true),
                        TextInput::make('nip_baru')
                            ->label('NIP Baru')
                            ->visible(fn (Get $get): bool => $get('identity_type') === IdentityType::Pegawai->value)
                            ->unique(ignoreRecord: true),

                        TextInput::make('sobat_id')
                            ->label('SOBAT ID')
                            ->visible(fn (Get $get): bool => $get('identity_type') === IdentityType::Mitra->value)
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Data Organisasi')
                    ->icon('heroicon-o-building-office')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => in_array($get('identity_type'), [
                        IdentityType::Pegawai->value,
                        IdentityType::Mitra->value,
                    ]))
                    ->schema([
                        TextInput::make('kd_satker')
                            ->label('Kode Satker')
                            ->maxLength(10),
                        TextInput::make('unit_kerja')
                            ->label('Unit Kerja'),
                        TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->visible(fn (Get $get): bool => $get('identity_type') === IdentityType::Pegawai->value),
                        TextInput::make('golongan')
                            ->label('Golongan')
                            ->visible(fn (Get $get): bool => $get('identity_type') === IdentityType::Pegawai->value)
                            ->maxLength(10),
                    ]),

                Section::make('Data Pribadi')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'LK' => 'Laki-laki',
                                'PR' => 'Perempuan',
                            ]),
                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir'),
                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir'),
                        TextInput::make('pendidikan')
                            ->label('Pendidikan'),
                        TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
