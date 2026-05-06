<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PortalApp;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortalAppPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PortalApp');
    }

    public function view(AuthUser $authUser, PortalApp $portalApp): bool
    {
        return $authUser->can('View:PortalApp');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PortalApp');
    }

    public function update(AuthUser $authUser, PortalApp $portalApp): bool
    {
        return $authUser->can('Update:PortalApp');
    }

    public function delete(AuthUser $authUser, PortalApp $portalApp): bool
    {
        return $authUser->can('Delete:PortalApp');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PortalApp');
    }

    public function restore(AuthUser $authUser, PortalApp $portalApp): bool
    {
        return $authUser->can('Restore:PortalApp');
    }

    public function forceDelete(AuthUser $authUser, PortalApp $portalApp): bool
    {
        return $authUser->can('ForceDelete:PortalApp');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PortalApp');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PortalApp');
    }

    public function replicate(AuthUser $authUser, PortalApp $portalApp): bool
    {
        return $authUser->can('Replicate:PortalApp');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PortalApp');
    }

}