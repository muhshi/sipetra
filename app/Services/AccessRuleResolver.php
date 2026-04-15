<?php

namespace App\Services;

use App\Enums\AccessRuleType;
use App\Enums\ClientAccessPolicy;
use App\Models\ClientAccessRule;
use App\Models\PassportClient;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class AccessRuleResolver
{
    /**
     * Evaluasi apakah user diizinkan login ke client ini.
     *
     * Alur:
     * 1. User non-aktif → selalu ditolak
     * 2. Tidak ada rule → cek access_policy (open = izinkan, restricted = tolak)
     * 3. Ada rule → salah satu cocok = izinkan, tidak ada cocok = tolak
     */
    public function isAllowed(Authenticatable|User $user, PassportClient $client): bool
    {
        if (! $user->is_active) {
            return false;
        }

        $rules = $client->accessRules;

        if ($rules->isEmpty()) {
            return $client->access_policy === ClientAccessPolicy::Open;
        }

        return $rules->contains(fn (ClientAccessRule $rule) => $this->matchesRule($user, $rule));
    }

    /**
     * Resolve client role untuk user yang ter-authorized.
     * Mencari rule pertama yang cocok dan punya client_role_id.
     */
    public function resolveClientRole(Authenticatable|User $user, PassportClient $client): ?string
    {
        $rules = $client->accessRules()->with('role')->get();

        $matchedRule = $rules->first(fn (ClientAccessRule $rule) => $this->matchesRule($user, $rule) && $rule->role);

        return $matchedRule?->role?->name;
    }

    /**
     * Cek apakah user cocok dengan satu rule.
     */
    private function matchesRule(Authenticatable|User $user, ClientAccessRule $rule): bool
    {
        return match ($rule->rule_type) {
            AccessRuleType::User => $user->getAuthIdentifier() == $rule->rule_value,
            AccessRuleType::SipetraRole => $user->hasRole($rule->rule_value),
            AccessRuleType::IdentityType => $user->identity_type?->value === $rule->rule_value,
        };
    }
}
