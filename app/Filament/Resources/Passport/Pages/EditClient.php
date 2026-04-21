<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Enums\ClientAccessPolicy;
use App\Filament\Resources\Passport\ClientResource;
use Illuminate\Database\Eloquent\Model;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\EditClient as BaseEditClient;

class EditClient extends BaseEditClient
{
    protected static string $resource = ClientResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Prevent scope wiping by providing existing scopes to the UseCase
        $data['scopes'] = $record->passportScopeGrants->map(function ($grant) {
            return "{$grant->resource->name}:{$grant->action->name}";
        })->toArray();

        $updatedRecord = parent::handleRecordUpdate($record, $data);
        $updatedRecord->forceFill([
            'access_policy' => $data['access_policy'] ?? ClientAccessPolicy::Restricted->value,
        ])->save();

        return $updatedRecord;
    }
}
