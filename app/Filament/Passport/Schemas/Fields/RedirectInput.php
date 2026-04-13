<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas\Fields;

use Filament\Forms\Components\TextInput;

class RedirectInput
{
    public static function make(): TextInput
    {
        return TextInput::make('redirect_uris')
            ->label(__('Callback URI'))
            ->helperText(__('The URI where the user will be redirected after authorization. Separate multiple URIs with commas.'))
            ->placeholder('https://your-app.com/callback')
            ->rules(['required_if:grant_type,authorization_code,implicit'])
            ->dehydrateStateUsing(fn ($state) => array_filter(array_map('trim', explode(',', (string) $state))));
    }
}
