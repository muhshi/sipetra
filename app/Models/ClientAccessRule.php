<?php

namespace App\Models;

use App\Enums\AccessRuleType;
use App\Models\Passport\Client;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'rule_type',
    'rule_value',
])]
class ClientAccessRule extends Model
{
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    protected function casts(): array
    {
        return [
            'rule_type' => AccessRuleType::class,
        ];
    }
}
