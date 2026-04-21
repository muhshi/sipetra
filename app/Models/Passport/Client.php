<?php

namespace App\Models\Passport;

use App\Enums\ClientAccessPolicy;
use App\Models\ClientAccessRule;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends \N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client
{
    public function getMorphClass(): string
    {
        // Keep the legacy morph type so existing scope grants continue to match.
        return \N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client::class;
    }

    public function accessRules(): HasMany
    {
        return $this->hasMany(ClientAccessRule::class, 'client_id');
    }

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'access_policy' => ClientAccessPolicy::class,
        ]);
    }
}
