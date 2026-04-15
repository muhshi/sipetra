<?php

declare(strict_types=1);

namespace App\Support\Passport;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TokenDisplayNameResolver
{
    public function execute(Model $token): string
    {
        $userId = $token->getAttribute('user_id');

        if (blank($userId)) {
            return '-';
        }

        $userModel = $this->getUserModelClass();

        if ($userModel === null) {
            return (string) $userId;
        }

        $user = $userModel::query()
            ->select(['id', 'name'])
            ->where((new $userModel)->getAuthIdentifierName(), $userId)
            ->first();

        return $user?->getAttribute('name') ?? (string) $userId;
    }

    public function applySearch(Builder $query, string $search): Builder
    {
        $userModel = $this->getUserModelClass();

        if ($userModel === null) {
            return $query->where('user_id', 'like', "%{$search}%");
        }

        $userInstance = new $userModel;
        $userKeyName = $userInstance->getAuthIdentifierName();
        $userTable = $userInstance->getTable();

        return $query->whereIn('user_id', function ($subQuery) use ($search, $userKeyName, $userTable, $userModel): void {
            $subQuery->from($userTable)
                ->select($userKeyName)
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    private function getUserModelClass(): ?string
    {
        $provider = config('auth.guards.api.provider');

        if ($provider === null) {
            return null;
        }

        $model = config("auth.providers.{$provider}.model");

        return is_string($model) ? $model : null;
    }
}
