<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientUserAccess extends Model
{
    protected $fillable = ['client_id', 'user_id', 'client_role_id'];

    /** @return BelongsTo<PassportClient, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(PassportClient::class, 'client_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ClientRole, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(ClientRole::class, 'client_role_id');
    }
}
