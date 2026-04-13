<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas\Fields;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

class SecretInput
{
    public static function make(): TextInput
    {
        return TextInput::make('secret')
            ->label(__('Client Secret'))
            ->password()
            ->revealable()
            ->readOnly()
            ->dehydrated(false)
            ->visible(fn ($state) => filled($state))
            ->helperText(__('Copy this secret. It will not be shown again after you leave this page.'))
            ->suffixAction(
                Action::make('copy')
                    ->icon('heroicon-m-clipboard')
                    ->alpineClickHandler(fn ($state) => "window.navigator.clipboard.writeText('$state'); \$tooltip('Copied to clipboard', { timeout: 1500 });")
            );
    }
}
