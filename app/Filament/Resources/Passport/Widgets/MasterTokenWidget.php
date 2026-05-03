<?php

namespace App\Filament\Resources\Passport\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class MasterTokenWidget extends Widget
{
    protected static string $view = 'filament.resources.passport.widgets.master-token-widget';

    public ?string $token = null;

    protected int|string|array $columnSpan = 'full';

    #[On('token-generated')]
    public function setToken($token)
    {
        $this->token = $token;
    }
}
