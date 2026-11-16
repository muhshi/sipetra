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
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $user->can('View:StatsOverview')
            || $user->can('view_stats_overview')
            || $user->can('view_StatsOverview');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pegawai', User::where('identity_type', IdentityType::Pegawai)->where('is_active', true)->count())
                ->description('PNS & PPPK Aktif')
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
