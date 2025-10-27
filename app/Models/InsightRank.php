<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsightRank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'min_points',
        'max_points',
        'icon',
        'description',
        'order',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'max_points' => 'integer',
    ];

    /**
     * Relationship: Rank has many user profiles
     */
    public function userProfiles()
    {
        return $this->hasMany(InsightUserProfile::class, 'current_rank_id');
    }

    /**
     * Scope: Ordered by order column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Get rank for specific points
     */
    public static function getRankForPoints(int $points)
    {
        return self::where('min_points', '<=', $points)
            ->where(function ($query) use ($points) {
                $query->where('max_points', '>=', $points)
                    ->orWhereNull('max_points');
            })
            ->orderBy('min_points', 'desc')
            ->first();
    }
}