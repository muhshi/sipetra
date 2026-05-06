<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PassportClient;
use Illuminate\Auth\Access\HandlesAuthorization;

class PassportClientPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PassportClient');
    }

    public function view(AuthUser $authUser, PassportClient $passportClient): bool
    {
        return $authUser->can('View:PassportClient');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PassportClient');
    }

    public function update(AuthUser $authUser, PassportClient $passportClient): bool
    {
        return $authUser->can('Update:PassportClient');
    }

    public function delete(AuthUser $authUser, PassportClient $passportClient): bool
    {
        return $authUser->can('Delete:PassportClient');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PassportClient');
    }

    public function restore(AuthUser $authUser, PassportClient $passportClient): bool
    {
        return $authUser->can('Restore:PassportClient');
    }

    public function forceDelete(AuthUser $authUser, PassportClient $passportClient): bool
    {
        return $authUser->can('ForceDelete:PassportClient');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PassportClient');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PassportClient');
    }

    public function replicate(AuthUser $authUser, PassportClient $passportClient): bool
    {
        return $authUser->can('Replicate:PassportClient');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PassportClient');
    }

}