<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Filament\Resources\Passport\ClientResource;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\ListClients as BaseListClients;

class ListClients extends BaseListClients
{
    protected static string $resource = ClientResource::class;

    public function getHeaderActions(): array
    {
        return array_merge([
            \Filament\Actions\Action::make('generate_master_token')
                ->label('Generate Master API Token')
                ->icon('heroicon-o-key')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Token M2M Master Data')
                ->modalDescription('Token ini akan memberikan akses ke API Master Data Sipetra (M2M). Setelah dihasilkan, token hanya akan ditampilkan SEKALI. Segera copy dan simpan ke .env ManajemenSurat!')
                ->action(function () {
                    // Cek apakah Personal Access Client sudah ada, jika belum otomatis buatkan
                    $clientRepository = app(\Laravel\Passport\ClientRepository::class);
                    $hasPersonalClient = false;

                    try {
                        $clientRepository->personalAccessClient(config('auth.guards.api.provider'));
                        $hasPersonalClient = true;
                    } catch (\RuntimeException $e) {
                        $hasPersonalClient = false;
                    }

                    if (! $hasPersonalClient) {
                        \Illuminate\Support\Facades\Artisan::call('passport:client', [
                            '--personal' => true,
                            '--name' => 'Sipetra Personal Access Client',
                            '--no-interaction' => true,
                        ]);
                    }

                    $user = auth()->user();
                    $token = $user->createToken('master-data-api');

                    \Filament\Notifications\Notification::make()
                        ->title('Master API Token Berhasil Dibuat! (COPY SEKARANG)')
                        ->success()
                        // Menggunakan persistent agar tidak hilang
                        ->persistent()
                        ->body('<div style="word-break: break-all; margin-top: 0.5rem; padding: 0.5rem; background: #1f2937; color: #fff; border-radius: 0.375rem; user-select: all;">' . $token->accessToken . '</div>')
                        ->send();
                }),
        ], parent::getHeaderActions());
    }
}
