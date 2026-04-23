<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\Pages;

use App\Enums\ClientAccessPolicy;
use App\Filament\Passport\Schemas\ExtendedClientWizardForm;
use App\Filament\Resources\Passport\ClientResource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use N3XT0R\FilamentPassportUi\Resources\ClientResource\Pages\CreateClient as BaseCreateClient;

class CreateClient extends BaseCreateClient
{
    protected static string $resource = ClientResource::class;

    public function form(Schema $schema): Schema
    {
        return ExtendedClientWizardForm::configure($schema);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Save access policy
        $record->forceFill([
            'access_policy' => $data['access_policy'] ?? ClientAccessPolicy::Restricted->value,
        ])->save();

        // Save access rules if present
        if (! empty($data['access_rules'])) {
            foreach ($data['access_rules'] as $ruleSet) {
                $ruleType = $ruleSet['rule_type'];
                $ruleValues = (array) ($ruleSet['rule_values'] ?? []);

                foreach ($ruleValues as $ruleValue) {
                    \App\Models\ClientAccessRule::create([
                        'client_id' => $record->getKey(),
                        'rule_type' => $ruleType,
                        'rule_value' => (string) $ruleValue,
                    ]);
                }
            }
        }

        return $record;
    }
}
