<?php

namespace App\Policies;

use App\Models\InsightComment;
use App\Models\User;

class InsightCommentPolicy
{
    /**
     * Determine if the user can view any comments.
     * Public: Anyone can view comments
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the comment.
     * Public: Anyone can view a single comment
     */
    public function view(?User $user, InsightComment $comment): bool
    {
        return true;
    }

    /**
     * Determine if the user can create comments.
     * Authenticated users only
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can comment
    }

    /**
     * Determine if the user can update the comment.
     * Owner OR Admin only
     */
    public function update(User $user, InsightComment $comment): bool
    {
        // Check if user is admin
        $adminIds = config('insight.admin_user_ids', []);
        if (in_array($user->id, $adminIds)) {
            return true;
        }

        // Check if user is owner
        return $user->id === $comment->user_id;
    }

    /**
     * Determine if the user can delete the comment.
     * Owner OR Admin only
     */
    public function delete(User $user, InsightComment $comment): bool
    {
        // Check if user is admin
        $adminIds = config('insight.admin_user_ids', []);
        if (in_array($user->id, $adminIds)) {
            return true;
        }

        // Check if user is owner
        return $user->id === $comment->user_id;
    }
}