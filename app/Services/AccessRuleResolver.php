<?php

namespace App\Services;

use App\Enums\AccessRuleType;
use App\Enums\ClientAccessPolicy;
use App\Models\Passport\Client;
use App\Models\User;

class AccessRuleResolver
{
    public function isAllowed(User $user, Client $client): bool
    {
        if (! $user->is_active) {
            return false;
        }

        $client->loadMissing('accessRules');

        $rules = $client->accessRules;

        if ($rules->isEmpty()) {
            return $client->access_policy === ClientAccessPolicy::Open;
        }

        return $rules->contains(fn ($rule) => $this->ruleMatches($user, $rule->rule_type, (string) $rule->rule_value));
    }

    private function ruleMatches(User $user, AccessRuleType|string|null $ruleType, string $ruleValue): bool
    {
        $ruleType = $ruleType instanceof AccessRuleType ? $ruleType : AccessRuleType::tryFrom((string) $ruleType);

        return match ($ruleType) {
            AccessRuleType::User => (string) $user->getKey() === $ruleValue,
            AccessRuleType::SipetraRole => $user->hasRole($ruleValue),
            AccessRuleType::IdentityType => (string) $user->identity_type?->value === $ruleValue,
            default => false,
        };
    }
}
