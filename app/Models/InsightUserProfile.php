<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsightUserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_points',
        'insight_count',
        'comment_count',
        'current_rank_id',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'insight_count' => 'integer',
        'comment_count' => 'integer',
    ];

    /**
     * Relationship: Profile belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Profile belongs to rank
     */
    public function currentRank()
    {
        return $this->belongsTo(InsightRank::class, 'current_rank_id');
    }

    /**
     * Calculate total points
     */
    public function calculatePoints(): int
    {
        return $this->insight_count + $this->comment_count;
    }

    /**
     * Update rank based on current points
     */
    public function updateRank(): void
    {
        $this->total_points = $this->calculatePoints();
        
        $rank = InsightRank::getRankForPoints($this->total_points);
        
        if ($rank) {
            $this->current_rank_id = $rank->id;
        }
        
        $this->save();
    }

    /**
     * Increment insight count
     * 
     * ⚠️ IMPORTANT: DO NOT CALL THIS METHOD DIRECTLY FROM CONTROLLERS!
     * 
     * This method is now called automatically by the Observer pattern:
     * - InsightObserver->created() 
     *   → PointService->handleInsightCreated() 
     *   → incrementInsightCount()
     * 
     * Using Observer ensures consistency and prevents forgetting to update points.
     */
    public function incrementInsightCount(): void
    {
        $this->increment('insight_count');
        $this->updateRank();
    }

    /**
     * Decrement insight count
     * 
     * ⚠️ IMPORTANT: DO NOT CALL THIS METHOD DIRECTLY FROM CONTROLLERS!
     * 
     * This method is now called automatically by the Observer pattern:
     * - InsightObserver->deleted() 
     *   → PointService->handleInsightDeleted() 
     *   → decrementInsightCount()
     */
    public function decrementInsightCount(): void
    {
        $this->decrement('insight_count');
        $this->updateRank();
    }

    /**
     * Increment comment count
     * 
     * ⚠️ IMPORTANT: DO NOT CALL THIS METHOD DIRECTLY FROM CONTROLLERS!
     * 
     * This method is now called automatically by the Observer pattern:
     * - InsightCommentObserver->created() 
     *   → PointService->handleCommentCreated() 
     *   → incrementCommentCount()
     */
    public function incrementCommentCount(): void
    {
        $this->increment('comment_count');
        $this->updateRank();
    }

    /**
     * Decrement comment count
     * 
     * ⚠️ IMPORTANT: DO NOT CALL THIS METHOD DIRECTLY FROM CONTROLLERS!
     * 
     * This method is now called automatically by the Observer pattern:
     * - InsightCommentObserver->deleted() 
     *   → PointService->handleCommentDeleted() 
     *   → decrementCommentCount()
     */
    public function decrementCommentCount(): void
    {
        $this->decrement('comment_count');
        $this->updateRank();
    }

    /**
     * Scope: Get top users for leaderboard
     */
    public function scopeLeaderboard($query, int $limit = 4)
    {
        return $query->orderBy('total_points', 'desc')->limit($limit);
    }

    /**
     * Get or create profile for user
     * 
     * This method is safe to call directly as it only retrieves/creates the profile.
     * It does not modify points or ranks.
     */
    public static function getOrCreateForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_points' => 0,
                'insight_count' => 0,
                'comment_count' => 0,
            ]
        );
    }
}