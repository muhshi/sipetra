<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\IdentityType;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
