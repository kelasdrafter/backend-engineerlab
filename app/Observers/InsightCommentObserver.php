<?php

namespace App\Observers;

use App\Models\InsightComment;
use App\Services\PointService;

class InsightCommentObserver
{
    /**
     * Handle the InsightComment "created" event.
     * 
     * Automatically triggered when InsightComment::create() is called.
     * Updates:
     * 1. Insight's comment_count (increment)
     * 2. User's points and rank
     */
    public function created(InsightComment $comment): void
    {
        // Update insight comment count
        $comment->insight->increment('comment_count');
        
        // Update user points and rank
        app(PointService::class)->handleCommentCreated($comment->user_id);
    }

    /**
     * Handle the InsightComment "deleted" event.
     * 
     * Automatically triggered when $comment->delete() is called.
     * Updates:
     * 1. Insight's comment_count (decrement)
     * 2. User's points and rank
     * 
     * Note: When parent comment deleted, replies are cascade deleted by DB FK.
     * This observer will be triggered for EACH deleted comment (parent + replies).
     */
    public function deleted(InsightComment $comment): void
    {
        // Update insight comment count
        // Check if insight still exists (in case of cascade delete)
        if ($comment->insight) {
            $comment->insight->decrement('comment_count');
        }
        
        // Update user points and rank
        app(PointService::class)->handleCommentDeleted($comment->user_id);
    }
}