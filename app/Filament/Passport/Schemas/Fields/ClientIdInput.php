<?php

declare(strict_types=1);

namespace App\Filament\Passport\Schemas\Fields;

use Filament\Forms\Components\TextInput;
use Webbingbrasil\FilamentCopyActions\Actions\CopyAction;

class ClientIdInput
{
    public static function make(): TextInput
    {
        return TextInput::make('id')
            ->label(__('Client ID'))
            ->readOnly()
            ->suffixAction(CopyAction::make());
    }
}
