<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class SsoAuditLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'client_id',
        'event',
        'ip_address',
        'user_agent',
        'description',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create an audit log entry safely without interrupting application flow.
     */
    public static function log(
        User|Authenticatable|null $user,
        ?string $clientId,
        string $event,
        ?string $description = null,
        ?Request $request = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): ?self {
        try {
            $req = $request ?? request();

            return self::create([
                'user_id' => $user?->getAuthIdentifier(),
                'client_id' => $clientId,
                'event' => $event,
                'ip_address' => $ipAddress ?? $req?->ip(),
                'user_agent' => $userAgent ?? $req?->userAgent(),
                'description' => $description,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
