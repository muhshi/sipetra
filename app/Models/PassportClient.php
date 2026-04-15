<?php

namespace App\Models;

use App\Enums\ClientAccessPolicy;
use App\Services\AccessRuleResolver;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\Scope;
use N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client as BaseClient;

class PassportClient extends BaseClient
{
    /**
     * Determine if the client should skip the authorization prompt.
     * Evaluasi dilakukan oleh AccessRuleResolver berdasarkan access_policy dan rules.
     *
     * @param  Scope[]  $scopes
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return app(AccessRuleResolver::class)->isAllowed($user, $this);
    }

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'access_policy' => ClientAccessPolicy::class,
        ]);
    }

    /** @return HasMany<ClientAccessRule, $this> */
    public function accessRules(): HasMany
    {
        return $this->hasMany(ClientAccessRule::class, 'client_id');
    }

    /** @return HasMany<ClientRole, $this> */
    public function clientRoles(): HasMany
    {
        return $this->hasMany(ClientRole::class, 'client_id');
    }

    /** @return HasMany<ClientUserAccess, $this> */
    public function userAccesses(): HasMany
    {
        return $this->hasMany(ClientUserAccess::class, 'client_id');
    }
}
