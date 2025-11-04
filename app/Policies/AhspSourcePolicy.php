<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RAB\AhspSource;
use Illuminate\Auth\Access\HandlesAuthorization;

class AhspSourcePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any AHSP sources.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // All authenticated users can view AHSP sources
        return true;
    }

    /**
     * Determine whether the user can view the AHSP source.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, AhspSource $ahspSource)
    {
        // All authenticated users can view any AHSP source
        return true;
    }

    /**
     * Determine whether the user can create AHSP sources.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Check if user has admin role
        // Adjust this based on your role system
        return $this->isAdmin($user);
        
        // Alternative: Allow all authenticated users
        // return true;
    }

    /**
     * Determine whether the user can update the AHSP source.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, AhspSource $ahspSource)
    {
        // Admin can update any AHSP source
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can update their own AHSP source
        return $user->id === $ahspSource->created_by;
    }

    /**
     * Determine whether the user can delete the AHSP source.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, AhspSource $ahspSource)
    {
        // Admin can delete any AHSP source
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can delete their own AHSP source
        return $user->id === $ahspSource->created_by;
    }

    /**
     * Determine whether the user can restore the AHSP source.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, AhspSource $ahspSource)
    {
        // Only admin can restore
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the AHSP source.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, AhspSource $ahspSource)
    {
        // Only admin can force delete
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can toggle active status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function toggleActive(User $user, AhspSource $ahspSource)
    {
        // Admin can toggle any AHSP source
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can toggle their own AHSP source
        return $user->id === $ahspSource->created_by;
    }

    /**
     * Determine whether the user can view usage statistics.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewStats(User $user, AhspSource $ahspSource)
    {
        // All authenticated users can view stats
        return true;
    }

    /**
     * Determine whether the user can use this AHSP source in their projects.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function use(User $user, AhspSource $ahspSource)
    {
        // Cannot use inactive AHSP source
        if (!$ahspSource->is_active) {
            return false;
        }

        // Cannot use soft-deleted AHSP source
        if ($ahspSource->trashed()) {
            return false;
        }

        // All authenticated users can use active AHSP sources
        return true;
    }

    /**
     * Check if user is admin
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    protected function isAdmin(User $user): bool
    {
        // Adjust this based on your role system
        // Examples:
        
        // If using Spatie Permission package:
        // return $user->hasRole('admin');
        
        // If using simple role field:
        // return $user->role === 'admin';
        
        // If using is_admin boolean field:
        // return $user->is_admin === true;
        
        // For now, return true to allow all operations during development
        // CHANGE THIS IN PRODUCTION!
        return true;
    }

    /**
     * Check if user is super admin
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    protected function isSuperAdmin(User $user): bool
    {
        // Adjust this based on your role system
        // Examples:
        
        // If using Spatie Permission package:
        // return $user->hasRole('super-admin');
        
        // If using simple role field:
        // return $user->role === 'super_admin';
        
        // For now, return false
        return false;
    }

    /**
     * Determine if the given AHSP source can be managed by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return bool
     */
    protected function canManage(User $user, AhspSource $ahspSource): bool
    {
        return $this->isAdmin($user) || $user->id === $ahspSource->created_by;
    }

    /**
     * Before method - runs before any other authorization check
     * Useful for super admin that can do everything
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, $ability)
    {
        // Super admin can do anything
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Return null to continue with other checks
        return null;
    }
}