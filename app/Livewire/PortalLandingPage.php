<?php

namespace App\Livewire;

use App\Models\PortalApp;
use App\Settings\PortalSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PortalLandingPage extends Component
{
    #[Layout('components.layouts.portal')]
    public function render(PortalSettings $settings)
    {
        $apps = PortalApp::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();

        return view('livewire.portal-landing-page', [
            'settings' => $settings,
            'apps' => $apps,
        ]);
    }
}
