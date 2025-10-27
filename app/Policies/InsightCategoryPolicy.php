<?php

namespace App\Policies;

use App\Models\InsightCategory;
use App\Models\User;

class InsightCategoryPolicy
{
    /**
     * Determine if the user can view any categories.
     * Public: Anyone can view categories
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the category.
     * Public: Anyone can view a single category
     */
    public function view(?User $user, InsightCategory $category): bool
    {
        return true;
    }

    /**
     * Determine if the user can create categories.
     * Admin only
     */
    public function create(User $user): bool
    {
        $adminIds = config('insight.admin_user_ids', []);
        return in_array($user->id, $adminIds);
    }

    /**
     * Determine if the user can update the category.
     * Admin only
     */
    public function update(User $user, InsightCategory $category): bool
    {
        $adminIds = config('insight.admin_user_ids', []);
        return in_array($user->id, $adminIds);
    }

    /**
     * Determine if the user can delete the category.
     * Admin only + Check if category has insights
     */
    public function delete(User $user, InsightCategory $category): bool
    {
        $adminIds = config('insight.admin_user_ids', []);
        
        if (!in_array($user->id, $adminIds)) {
            return false;
        }

        // Don't allow deletion if category has insights
        return $category->insights()->count() === 0;
    }
}