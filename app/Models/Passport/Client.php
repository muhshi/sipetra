<?php

namespace App\Models\Passport;

use App\Enums\ClientAccessPolicy;
use App\Models\ClientAccessRule;
use App\Models\ClientRole;
use App\Models\ClientUserAccess;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\Scope;

class Client extends \N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client
{
    /**
     * Determine if the client should skip the authorization prompt.
     * Jika user tidak terdaftar dalam daftar akses klien ini, tampilkan view penolakan.
     *
     * @param  Scope[]  $scopes
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return ClientUserAccess::where('client_id', $this->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->exists();
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
