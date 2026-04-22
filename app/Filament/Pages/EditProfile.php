<?php

namespace App\Filament\Pages;

use App\Enums\IdentityType;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Data utama akun Anda untuk masuk ke sistem.')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->same('passwordConfirmation'),
                        TextInput::make('passwordConfirmation')
                            ->label('Konfirmasi Password Baru')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required(fn (Get $get) => filled($get('password')))
                            ->dehydrated(false),
                    ]),

                Section::make('Profil Kepegawaian')
                    ->description('Data identitas dan kepegawaian Anda (Read-Only).')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('nip')
                                    ->label('NIP Lama')
                                    ->disabled(),
                                TextInput::make('nip_baru')
                                    ->label('NIP Baru')
                                    ->disabled(),
                                TextInput::make('identity_type')
                                    ->label('Tipe User')
                                    ->formatStateUsing(fn ($state) => $state instanceof IdentityType ? $state->label() : $state)
                                    ->disabled(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('employeeProfile.tmt_cpns')
                                    ->label('TMT CPNS')
                                    ->disabled(),
                                TextInput::make('employeeProfile.tmt_pns')
                                    ->label('TMT PNS')
                                    ->disabled(),
                                TextInput::make('employeeProfile.tmt_golongan')
                                    ->label('TMT Golongan')
                                    ->disabled(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('employeeProfile.tmt_jabatan')
                                    ->label('TMT Jabatan')
                                    ->disabled(),
                                TextInput::make('employeeProfile.status_pegawai')
                                    ->label('Status Pegawai')
                                    ->disabled(),
                                TextInput::make('employeeProfile.agama')
                                    ->label('Agama')
                                    ->disabled(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('jabatan')
                                    ->disabled(),
                                TextInput::make('unit_kerja')
                                    ->disabled(),
                                TextInput::make('golongan')
                                    ->disabled(),
                                TextInput::make('masa_kerja')
                                    ->label('Masa Kerja')
                                    ->placeholder(fn ($record) => $record?->masa_kerja)
                                    ->disabled(),
                            ]),
                    ])
                    ->visible(fn () => in_array(auth()->user()->identity_type, [IdentityType::Pegawai, IdentityType::Admin])),

                Section::make('Data Personal')
                    ->description('Informasi kontak dan data pribadi yang dapat Anda perbarui.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Nomor Telepon')
                                    ->tel(),
                                TextInput::make('tempat_lahir')
                                    ->label('Tempat Lahir'),
                            ]),
                        FileUpload::make('avatar_url')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('avatars')
                            ->visibility('public'),
                    ]),
            ]);
    }
}
