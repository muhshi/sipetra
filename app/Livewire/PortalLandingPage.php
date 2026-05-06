<?php

namespace App\Livewire;

use App\Models\PortalApp;
use App\Settings\PortalSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PortalLandingPage extends Component
{
    public $apps;
    public $settings;

    public function mount(PortalSettings $settings)
    {
        $this->settings = $settings;
        $this->apps = PortalApp::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();
    }

    #[Layout('components.layouts.guest')]
    public function render()
    {
        return view('livewire.portal-landing-page');
    }
}
