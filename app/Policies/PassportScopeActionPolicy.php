<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeAction;
use Illuminate\Auth\Access\HandlesAuthorization;

class PassportScopeActionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PassportScopeAction');
    }

    public function view(AuthUser $authUser, PassportScopeAction $passportScopeAction): bool
    {
        return $authUser->can('View:PassportScopeAction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PassportScopeAction');
    }

    public function update(AuthUser $authUser, PassportScopeAction $passportScopeAction): bool
    {
        return $authUser->can('Update:PassportScopeAction');
    }

    public function delete(AuthUser $authUser, PassportScopeAction $passportScopeAction): bool
    {
        return $authUser->can('Delete:PassportScopeAction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PassportScopeAction');
    }

    public function restore(AuthUser $authUser, PassportScopeAction $passportScopeAction): bool
    {
        return $authUser->can('Restore:PassportScopeAction');
    }

    public function forceDelete(AuthUser $authUser, PassportScopeAction $passportScopeAction): bool
    {
        return $authUser->can('ForceDelete:PassportScopeAction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PassportScopeAction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PassportScopeAction');
    }

    public function replicate(AuthUser $authUser, PassportScopeAction $passportScopeAction): bool
    {
        return $authUser->can('Replicate:PassportScopeAction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PassportScopeAction');
    }

}