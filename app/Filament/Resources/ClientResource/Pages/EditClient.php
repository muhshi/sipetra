<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use Filament\Facades\Filament;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use N3XT0R\FilamentPassportUi\Resources\ClientResource;
use N3XT0R\LaravelPassportAuthorizationCore\Application\UseCases\Client\EditClientUseCase;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['redirect_uris']) && is_array($data['redirect_uris'])) {
            $data['redirect_uri'] = $data['redirect_uris'][0] ?? null;
        }

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Edit Client Details')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Aplikasi')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('redirect_uri')
                        ->label('Redirect URI')
                        ->helperText('Ubah jika URL callback berubah.')
                        ->url()
                        ->required(),
                ])->columns(2),
        ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $actor = Filament::auth()->user();

        $updateData = [
            'name' => $data['name'],
            'redirect_uris' => [rtrim($data['redirect_uri'], '/')],
        ];

        return app(EditClientUseCase::class)->execute(
            client: $record,
            data: $updateData,
            actor: $actor,
        );
    }
}
