<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RAB\MasterAhsp;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterAhspPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any master AHSP.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // All authenticated users can view master AHSP
        return true;
    }

    /**
     * Determine whether the user can view the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, MasterAhsp $masterAhsp)
    {
        // All authenticated users can view any master AHSP
        return true;
    }

    /**
     * Determine whether the user can create master AHSP.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Check if user has permission to create master AHSP
        // Adjust this based on your role system
        return $this->isAdmin($user);
        
        // Alternative: Allow all authenticated users
        // return true;
    }

    /**
     * Determine whether the user can update the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, MasterAhsp $masterAhsp)
    {
        // Admin can update any master AHSP
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can update their own master AHSP
        return $user->id === $masterAhsp->created_by;
    }

    /**
     * Determine whether the user can delete the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, MasterAhsp $masterAhsp)
    {
        // Cannot delete if being used
        if ($this->isBeingUsed($masterAhsp)) {
            return false;
        }

        // Admin can delete any master AHSP
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can delete their own master AHSP
        return $user->id === $masterAhsp->created_by;
    }

    /**
     * Determine whether the user can restore the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, MasterAhsp $masterAhsp)
    {
        // Admin can restore any master AHSP
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can restore their own master AHSP
        return $user->id === $masterAhsp->created_by;
    }

    /**
     * Determine whether the user can permanently delete the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, MasterAhsp $masterAhsp)
    {
        // Only admin can force delete
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can duplicate the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function duplicate(User $user, MasterAhsp $masterAhsp)
    {
        // All authenticated users can duplicate master AHSP
        // The duplicated AHSP will belong to them
        return true;
    }

    /**
     * Determine whether the user can calculate price for the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function calculatePrice(User $user, MasterAhsp $masterAhsp)
    {
        // All authenticated users can calculate price
        return true;
    }

    /**
     * Determine whether the user can view breakdown of the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewBreakdown(User $user, MasterAhsp $masterAhsp)
    {
        // All authenticated users can view breakdown
        return true;
    }

    /**
     * Determine whether the user can use this master AHSP in their projects.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function use(User $user, MasterAhsp $masterAhsp)
    {
        // Cannot use inactive master AHSP
        if (!$masterAhsp->is_active) {
            return false;
        }

        // Cannot use soft-deleted master AHSP
        if ($masterAhsp->trashed()) {
            return false;
        }

        // Check if AHSP source is active
        if ($masterAhsp->ahspSource && !$masterAhsp->ahspSource->is_active) {
            return false;
        }

        // All authenticated users can use active master AHSP
        return true;
    }

    /**
     * Determine whether the user can edit composition items.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function editComposition(User $user, MasterAhsp $masterAhsp)
    {
        // Cannot edit if being used
        if ($this->isBeingUsed($masterAhsp)) {
            return false;
        }

        // Admin can edit any master AHSP composition
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can edit their own master AHSP composition
        return $user->id === $masterAhsp->created_by;
    }

    /**
     * Determine whether the user can toggle active status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function toggleActive(User $user, MasterAhsp $masterAhsp)
    {
        // Admin can toggle any master AHSP
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can toggle their own master AHSP
        return $user->id === $masterAhsp->created_by;
    }

    /**
     * Determine whether the user can view usage statistics.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewUsage(User $user, MasterAhsp $masterAhsp)
    {
        // Admin can view usage of any master AHSP
        if ($this->isAdmin($user)) {
            return true;
        }

        // User can view usage of their own master AHSP
        return $user->id === $masterAhsp->created_by;
    }

    /**
     * Determine whether the user can export the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function export(User $user, MasterAhsp $masterAhsp)
    {
        // All authenticated users can export master AHSP
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
     * Check if master AHSP is being used in projects or templates
     *
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return bool
     */
    protected function isBeingUsed(MasterAhsp $masterAhsp): bool
    {
        $usageCount = $masterAhsp->projectAhsp()->count() 
            + $masterAhsp->templateItems()->count();

        return $usageCount > 0;
    }

    /**
     * Determine if the given master AHSP can be managed by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return bool
     */
    protected function canManage(User $user, MasterAhsp $masterAhsp): bool
    {
        return $this->isAdmin($user) || $user->id === $masterAhsp->created_by;
    }

    /**
     * Determine if the user owns the master AHSP.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RAB\MasterAhsp  $masterAhsp
     * @return bool
     */
    protected function owns(User $user, MasterAhsp $masterAhsp): bool
    {
        return $user->id === $masterAhsp->created_by;
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