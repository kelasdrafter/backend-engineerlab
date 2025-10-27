<?php

namespace App\Services;

use App\Models\InsightComment;
use App\Models\InsightCommentMention;

class MentionService
{
    /**
     * Process mentions for a comment
     * Saves mentioned users to database
     *
     * @param InsightComment $comment
     * @param array $userIds
     * @return void
     */
    public function processMentions(InsightComment $comment, array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        // Remove duplicates
        $userIds = array_unique($userIds);

        // Validate that users exist
        $validUserIds = \App\Models\User::whereIn('id', $userIds)->pluck('id')->toArray();

        // Create mention records
        foreach ($validUserIds as $userId) {
            InsightCommentMention::firstOrCreate([
                'comment_id' => $comment->id,
                'mentioned_user_id' => $userId,
            ]);
        }
    }

    /**
     * Clear all mentions for a comment
     *
     * @param InsightComment $comment
     * @return void
     */
    public function clearMentions(InsightComment $comment): void
    {
        InsightCommentMention::where('comment_id', $comment->id)->delete();
    }

    /**
     * Update mentions for a comment
     * Clears existing mentions and creates new ones
     *
     * @param InsightComment $comment
     * @param array $userIds
     * @return void
     */
    public function updateMentions(InsightComment $comment, array $userIds): void
    {
        // Clear existing mentions
        $this->clearMentions($comment);

        // Process new mentions
        $this->processMentions($comment, $userIds);
    }

    /**
     * Extract mentioned user IDs from comment text
     * Looks for @username or @user_id patterns
     * 
     * NOTE: This is a basic implementation. 
     * In production, you might want to use a more sophisticated parser.
     *
     * @param string $commentText
     * @return array
     */
    public function extractMentionedUserIds(string $commentText): array
    {
        // Pattern: @username or @user_id
        // Example: "@john", "@user123", "@jane_doe"
        preg_match_all('/@(\w+)/', $commentText, $matches);
        
        if (empty($matches[1])) {
            return [];
        }

        $usernames = $matches[1];
        
        // Try to find users by name or username
        // Adjust according to your User model fields
        $userIds = \App\Models\User::whereIn('name', $usernames)
            ->orWhereIn('username', $usernames) // If you have username field
            ->pluck('id')
            ->toArray();

        return $userIds;
    }

    /**
     * Get mentioned users for a comment
     *
     * @param InsightComment $comment
     * @return \Illuminate\Support\Collection
     */
    public function getMentionedUsers(InsightComment $comment)
    {
        return $comment->mentionedUsers;
    }

    /**
     * Check if user is mentioned in a comment
     *
     * @param InsightComment $comment
     * @param int $userId
     * @return bool
     */
    public function isUserMentioned(InsightComment $comment, int $userId): bool
    {
        return InsightCommentMention::where('comment_id', $comment->id)
            ->where('mentioned_user_id', $userId)
            ->exists();
    }
}