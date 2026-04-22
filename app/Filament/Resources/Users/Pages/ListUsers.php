<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\IdentityType;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_pegawai')
                ->label('Sync Data Pegawai')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    Artisan::call('app:update-employee-profiles');
                    Notification::make()
                        ->title('Sinkronisasi Selesai')
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()->hasRole('super_admin')),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(User::query()->count()),
            'pegawai' => Tab::make('Pegawai')
                ->badge(User::query()->where('identity_type', IdentityType::Pegawai)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('identity_type', IdentityType::Pegawai)),
            'mitra' => Tab::make('Mitra')
                ->badge(User::query()->where('identity_type', IdentityType::Mitra)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('identity_type', IdentityType::Mitra)),
            'admin' => Tab::make('Admin')
                ->badge(User::query()->where('identity_type', IdentityType::Admin)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('identity_type', IdentityType::Admin)),
        ];
    }
}
