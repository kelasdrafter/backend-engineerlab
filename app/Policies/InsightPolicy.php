<?php

namespace App\Policies;

use App\Models\Insight;
use App\Models\User;

class InsightPolicy
{
    /**
     * Determine if the user can view any insights.
     * Public: Anyone can view insights (no auth required)
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the insight.
     * Public: Anyone can view a single insight
     */
    public function view(?User $user, Insight $insight): bool
    {
        return true;
    }

    /**
     * Determine if the user can create insights.
     * Authenticated users only
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create
    }

    /**
     * Determine if the user can update the insight.
     * Owner OR Admin only
     */
    public function update(User $user, Insight $insight): bool
    {
        // Check if user is admin
        $adminIds = config('insight.admin_user_ids', []);
        if (in_array($user->id, $adminIds)) {
            return true;
        }

        // Check if user is owner
        return $user->id === $insight->user_id;
    }

    /**
     * Determine if the user can delete the insight.
     * Owner OR Admin only
     */
    public function delete(User $user, Insight $insight): bool
    {
        // Check if user is admin
        $adminIds = config('insight.admin_user_ids', []);
        if (in_array($user->id, $adminIds)) {
            return true;
        }

        // Check if user is owner
        return $user->id === $insight->user_id;
    }
}