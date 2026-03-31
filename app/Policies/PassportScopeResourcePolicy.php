<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeResource;
use Illuminate\Auth\Access\HandlesAuthorization;

class PassportScopeResourcePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PassportScopeResource');
    }

    public function view(AuthUser $authUser, PassportScopeResource $passportScopeResource): bool
    {
        return $authUser->can('View:PassportScopeResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PassportScopeResource');
    }

    public function update(AuthUser $authUser, PassportScopeResource $passportScopeResource): bool
    {
        return $authUser->can('Update:PassportScopeResource');
    }

    public function delete(AuthUser $authUser, PassportScopeResource $passportScopeResource): bool
    {
        return $authUser->can('Delete:PassportScopeResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PassportScopeResource');
    }

    public function restore(AuthUser $authUser, PassportScopeResource $passportScopeResource): bool
    {
        return $authUser->can('Restore:PassportScopeResource');
    }

    public function forceDelete(AuthUser $authUser, PassportScopeResource $passportScopeResource): bool
    {
        return $authUser->can('ForceDelete:PassportScopeResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PassportScopeResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PassportScopeResource');
    }

    public function replicate(AuthUser $authUser, PassportScopeResource $passportScopeResource): bool
    {
        return $authUser->can('Replicate:PassportScopeResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PassportScopeResource');
    }

}