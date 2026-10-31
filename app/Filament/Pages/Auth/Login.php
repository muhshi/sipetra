<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use SensitiveParameter;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('NIP / Email / Username / SOBAT ID')
            ->placeholder('NIP, Email, Username, atau SOBAT ID')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login = trim($data['email']);

        $user = User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('nip', $login)
                ->orWhere('nip_baru', $login)
                ->orWhere('sobat_id', $login);

            if (! str_contains($login, '@')) {
                $query->orWhere('email', $login.'@bps.go.id');
            }
        })->first();

        return [
            'email' => $user?->email ?? $login,
            'password' => $data['password'],
            'is_active' => true,
        ];
    }
}
