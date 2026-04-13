<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\Scope;
use N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client as BaseClient;

class PassportClient extends BaseClient
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
}
