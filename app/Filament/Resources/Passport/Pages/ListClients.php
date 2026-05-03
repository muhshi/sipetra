<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Filament\Resources\Passport\ClientResource;
use App\Filament\Resources\Passport\Widgets\MasterTokenWidget;
use Filament\Actions\Action;
use Laravel\Passport\ClientRepository;
use Livewire\Component;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\ListClients as BaseListClients;

class ListClients extends BaseListClients
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            MasterTokenWidget::class,
        ];
    }

    public function getHeaderActions(): array
    {
        return array_merge([
            Action::make('generate_master_token')
                ->label('Generate Master API Token')
                ->icon('heroicon-o-key')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Token M2M Master Data')
                ->modalDescription('Token ini akan memberikan akses ke API Master Data Sipetra (M2M). Setelah dihasilkan, token hanya akan ditampilkan SEKALI di bagian atas tabel. Segera copy dan simpan ke .env Aplikasi Client!')
                ->action(function (Component $livewire) {
                    // Cek apakah Personal Access Client sudah ada, jika belum otomatis buatkan
                    $clientRepository = app(ClientRepository::class);
                    $hasPersonalClient = false;

                    try {
                        $clientRepository->personalAccessClient(config('auth.guards.api.provider'));
                        $hasPersonalClient = true;
                    } catch (\RuntimeException $e) {
                        $hasPersonalClient = false;
                    }

                    if (! $hasPersonalClient) {
                        $clientRepository->createPersonalAccessGrantClient(
                            'Sipetra Personal Access Client',
                            config('auth.guards.api.provider')
                        );
                    }

                    $user = auth()->user();
                    $token = $user->createToken('master-data-api');

                    $livewire->dispatch('token-generated', token: $token->accessToken);
                }),
        ], parent::getHeaderActions());
    }
}
