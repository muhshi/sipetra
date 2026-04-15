<?php

namespace App\Models;

use App\Enums\AccessRuleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAccessRule extends Model
{
    protected $fillable = [
        'client_id',
        'rule_type',
        'rule_value',
        'client_role_id',
    ];

    protected function casts(): array
    {
        return [
            'rule_type' => AccessRuleType::class,
        ];
    }

    /** @return BelongsTo<PassportClient, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(PassportClient::class, 'client_id');
    }

    /** @return BelongsTo<ClientRole, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(ClientRole::class, 'client_role_id');
    }
}
