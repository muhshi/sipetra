<?php

namespace App\Livewire;

use App\Models\PortalApp;
use App\Settings\PortalSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PortalLandingPage extends Component
{
    #[Layout('components.layouts.portal')]
    public function render(PortalSettings $settings): View
    {
        $apps = PortalApp::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();

        $categories = $apps->pluck('category')->filter()->unique()->values()->all();

        // Attach cached health data to each app
        $appsWithHealth = $apps->map(function (PortalApp $app) {
            $health = Cache::get('app_health_'.$app->id, [
                'status' => 'online', // default optimistic
                'latency_ms' => null,
                'checked_at' => null,
            ]);
            $app->health_status = $health['status'] ?? 'online';
            $app->health_latency = $health['latency_ms'] ?? null;

            return $app;
        });

        return view('livewire.portal-landing-page', [
            'settings' => $settings,
            'apps' => $appsWithHealth,
            'categories' => $categories,
        ]);
    }
}
