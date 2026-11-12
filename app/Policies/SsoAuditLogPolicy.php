<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SsoAuditLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SsoAuditLogPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser, string $ability): ?bool
    {
        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('super_admin')) {
            return true;
        }

        if (method_exists($authUser, 'hasRole') && $authUser->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SsoAuditLog');
    }

    public function view(AuthUser $authUser, SsoAuditLog $ssoAuditLog): bool
    {
        return $authUser->can('View:SsoAuditLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, SsoAuditLog $ssoAuditLog): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, SsoAuditLog $ssoAuditLog): bool
    {
        return $authUser->can('Delete:SsoAuditLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SsoAuditLog');
    }
}
