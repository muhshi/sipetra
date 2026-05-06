<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pegawai', \App\Models\User::where('identity_type', \App\Enums\IdentityType::Pegawai)->count())
                ->description('PNS & PPPK')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Total Mitra', \App\Models\User::where('identity_type', \App\Enums\IdentityType::Mitra)->count())
                ->description('Mitra Statistik')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Aplikasi Aktif', \App\Models\PortalApp::where('is_active', true)->count())
                ->description('Portal Aplikasi')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),
        ];
    }
}
