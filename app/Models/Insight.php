<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Insight extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'view_count',
        'comment_count',
    ];

    protected $casts = [
        'view_count' => 'integer',
        'comment_count' => 'integer',
    ];

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * Relationship: Insight belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Insight belongs to category
     */
    public function category()
    {
        return $this->belongsTo(InsightCategory::class, 'category_id');
    }

    /**
     * Relationship: Insight has many media
     */
    public function media()
    {
        return $this->hasMany(InsightMedia::class);
    }

    /**
     * Relationship: Insight has many comments
     */
    public function comments()
    {
        return $this->hasMany(InsightComment::class);
    }

    /**
     * Get only parent comments (not replies)
     */
    public function parentComments()
    {
        return $this->hasMany(InsightComment::class)->whereNull('parent_id');
    }

    /**
     * Increment view count
     * 
     * ✅ This method is safe to call directly from controllers.
     * View count is not part of the point system and doesn't need Observer.
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Update comment count cache
     * 
     * ⚠️ DEPRECATED: DO NOT CALL THIS METHOD DIRECTLY!
     * 
     * The comment_count is now automatically updated by InsightCommentObserver:
     * - When comment created: InsightCommentObserver->created() → insight->increment('comment_count')
     * - When comment deleted: InsightCommentObserver->deleted() → insight->decrement('comment_count')
     * 
     * This method is kept for backward compatibility or manual recalculation if needed.
     * 
     * @deprecated Use Observer pattern instead
     */
    public function updateCommentCount()
    {
        $this->comment_count = $this->comments()->count();
        $this->save();
    }
}