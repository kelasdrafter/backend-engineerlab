<?php

namespace App\Services;

use App\Models\InsightUserProfile;
use App\Models\InsightRank;

class PointService
{
    /**
     * Handle insight created event.
     * Increments user's insight count and updates rank.
     */
    public function handleInsightCreated(int $userId): void
    {
        $profile = $this->getOrCreateProfile($userId);
        $profile->incrementInsightCount();
    }

    /**
     * Handle insight deleted event.
     * Decrements user's insight count and updates rank.
     */
    public function handleInsightDeleted(int $userId): void
    {
        $profile = $this->getOrCreateProfile($userId);
        $profile->decrementInsightCount();
    }

    /**
     * Handle comment created event.
     * Increments user's comment count and updates rank.
     */
    public function handleCommentCreated(int $userId): void
    {
        $profile = $this->getOrCreateProfile($userId);
        $profile->incrementCommentCount();
    }

    /**
     * Handle comment deleted event.
     * Decrements user's comment count and updates rank.
     */
    public function handleCommentDeleted(int $userId): void
    {
        $profile = $this->getOrCreateProfile($userId);
        $profile->decrementCommentCount();
    }

    /**
     * Get or create user profile.
     * 
     * @param int $userId
     * @return InsightUserProfile
     */
    private function getOrCreateProfile(int $userId): InsightUserProfile
    {
        return InsightUserProfile::getOrCreateForUser($userId);
    }

    /**
     * Recalculate all points for a user (manual recalculation).
     * Useful for fixing inconsistencies or migrations.
     * 
     * @param int $userId
     * @return void
     */
    public function recalculateUserPoints(int $userId): void
    {
        $profile = $this->getOrCreateProfile($userId);
        
        // Recalculate from actual data
        $insightCount = \App\Models\Insight::where('user_id', $userId)->count();
        $commentCount = \App\Models\InsightComment::where('user_id', $userId)->count();
        
        $profile->insight_count = $insightCount;
        $profile->comment_count = $commentCount;
        $profile->updateRank();
    }
}