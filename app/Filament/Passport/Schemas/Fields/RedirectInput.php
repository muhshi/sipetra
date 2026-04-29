<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas\Fields;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class RedirectInput
{
    public static function make(): Repeater
    {
        return Repeater::make('redirect_uris')
            ->label(__('Callback URIs'))
            ->helperText(__('Tambahkan satu atau lebih URI. Contoh: URL server produksi dan URL lokal bisa didaftarkan sekaligus.'))
            ->simple(
                TextInput::make('uri')
                    ->label(__('Callback URI'))
                    ->placeholder('https://domain-anda.com/auth/sipetra/callback')
                    ->url()
                    ->required()
            )
            ->defaultItems(1)
            ->addActionLabel(__('Tambah URI'))
            ->reorderable(false)
            ->columnSpanFull();
    }
}
