<?php

namespace App\Filament\Widgets;

use App\Enums\IdentityType;
use App\Models\PortalApp;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'operator']) ?? false;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pegawai', User::where('identity_type', IdentityType::Pegawai)->count())
                ->description('PNS & PPPK')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Total Mitra', User::where('identity_type', IdentityType::Mitra)->count())
                ->description('Mitra Statistik')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Aplikasi Aktif', PortalApp::where('is_active', true)->count())
                ->description('Portal Aplikasi')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),
        ];
    }
}
