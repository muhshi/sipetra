<?php

namespace App\Filament\Pages;

use App\Enums\IdentityType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Akun')
                    ->description('Data utama akun Anda untuk masuk ke sistem.')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
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
                                    ->placeholder(fn ($record) => $record->masa_kerja)
                                    ->disabled(),
                            ]),
                    ])
                    ->visible(fn () => auth()->user()->identity_type === IdentityType::Pegawai),

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
