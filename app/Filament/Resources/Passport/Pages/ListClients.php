<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Filament\Resources\Passport\ClientResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Laravel\Passport\ClientRepository;
use Livewire\Attributes\On;
use Livewire\Component;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\ListClients as BaseListClients;

class ListClients extends BaseListClients
{
    protected static string $resource = ClientResource::class;

    #[On('show-token-modal')]
    public function showTokenModal(string $token): void
    {
        $this->mountAction('show_generated_token', ['token' => $token]);
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
                ->modalDescription('Token ini akan memberikan akses ke API Master Data Sipetra (M2M). Setelah dihasilkan, token hanya akan ditampilkan SEKALI. Segera copy dan simpan ke .env Aplikasi Client!')
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

                    $livewire->dispatch('show-token-modal', token: $token->accessToken);
                }),

            Action::make('show_generated_token')
                ->hidden()
                ->modalHeading('Master API Token Berhasil Dibuat!')
                ->modalDescription('Silakan salin token di bawah ini dan simpan di .env Aplikasi Client. Token ini tidak akan ditampilkan lagi setelah Anda menutup jendela ini.')
                ->modalSubmitAction(false)
                ->modalCancelAction(fn ($action) => $action->label('Tutup'))
                ->infolist(function (array $arguments) {
                    return [
                        TextEntry::make('token')
                            ->hiddenLabel()
                            ->state(fn () => $arguments['token'] ?? '')
                            ->copyable()
                            ->copyMessage('Token berhasil disalin!')
                            ->copyMessageDuration(2000)
                            ->extraAttributes(['style' => 'word-break: break-all; font-family: monospace; background: #f3f4f6; padding: 1rem; border-radius: 0.5rem;'])
                            ->columnSpanFull(),
                    ];
                }),
        ], parent::getHeaderActions());
    }
}
