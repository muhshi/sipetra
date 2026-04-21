<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas\Fields;

use Filament\Forms\Components\Textarea;

class RedirectInput
{
    public static function make(): Textarea
    {
        return Textarea::make('redirect_uris')
            ->label(__('Callback URIs'))
            ->placeholder('https://your-app.com/callback')
            ->helperText(__('Separate multiple URIs with commas.'))
            ->rules(['required_if:grant_type,authorization_code,implicit'])
            ->dehydrateStateUsing(fn ($state) => array_filter(array_map('trim', explode(',', (string) $state))))
            ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
            ->autosize()
            ->columnSpanFull();
    }
}
