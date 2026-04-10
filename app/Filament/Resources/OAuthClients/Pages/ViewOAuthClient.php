<?php

namespace App\Filament\Resources\OAuthClients\Pages;

use App\Filament\Resources\OAuthClients\OAuthClientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewOAuthClient extends ViewRecord
{
    protected static string $resource = OAuthClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        $plainSecret = session('oauth_client_secret');
        $secretClientId = session('oauth_client_id');
        $showSecret = $plainSecret && $secretClientId === $this->record->getKey();

        return $schema
            ->components([
                Section::make('Kredensial Client')
                    ->icon('heroicon-o-key')
                    ->columns(2)
                    ->visible($showSecret)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Client ID')
                            ->copyable()
                            ->icon('heroicon-o-clipboard')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('plain_secret')
                            ->label('Client Secret')
                            ->state($plainSecret ?? '')
                            ->copyable()
                            ->icon('heroicon-o-clipboard')
                            ->weight('bold')
                            ->color('danger')
                            ->helperText('⚠️ SALIN SEKARANG! Secret ini hanya ditampilkan sekali dan tidak bisa dilihat lagi.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Detail Client')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Client ID')
                            ->copyable()
                            ->visible(! $showSecret),

                        TextEntry::make('name')
                            ->label('Nama Client'),

                        TextEntry::make('owner.name')
                            ->label('Owner')
                            ->placeholder('— tidak ada —'),

                        TextEntry::make('grant_types')
                            ->label('Grant Types')
                            ->badge()
                            ->color('info')
                            ->state(fn ($record): array => $record->grant_types ?? []),

                        TextEntry::make('redirect_uris')
                            ->label('Redirect URIs')
                            ->state(fn ($record): array => $record->redirect_uris ?? [])
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('— tidak ada —')
                            ->columnSpanFull(),

                        IconEntry::make('revoked')
                            ->label('Revoked')
                            ->boolean()
                            ->trueIcon('heroicon-o-x-circle')
                            ->falseIcon('heroicon-o-check-circle')
                            ->trueColor('danger')
                            ->falseColor('success'),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime('d M Y H:i'),
                    ]),
            ]);
    }
}
