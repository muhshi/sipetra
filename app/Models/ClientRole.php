<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientRole extends Model
{
    protected $fillable = ['client_id', 'name', 'description'];

    /** @return BelongsTo<PassportClient, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(PassportClient::class, 'client_id');
    }

    /** @return HasMany<ClientUserAccess, $this> */
    public function userAccesses(): HasMany
    {
        return $this->hasMany(ClientUserAccess::class);
    }
}
