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
            ->placeholder("https://domain-anda.com/auth/sipetra/callback,\nhttps://localhost/auth/sipetra/callback")
            ->helperText(__('Pisahkan beberapa URI dengan koma. Contoh: URL server dan URL lokal bisa didaftarkan sekaligus.'))
            ->rules(['required_if:grant_type,authorization_code,implicit'])
            ->dehydrateStateUsing(fn ($state) => array_filter(array_map('trim', explode(',', (string) $state))))
            ->formatStateUsing(fn ($state) => is_array($state) ? implode(",\n", $state) : $state)
            ->autosize()
            ->columnSpanFull();
    }
}
