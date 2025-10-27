<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsightComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'insight_id',
        'user_id',
        'parent_id',
        'comment',
    ];

    /**
     * Relationship: Comment belongs to insight
     */
    public function insight()
    {
        return $this->belongsTo(Insight::class);
    }

    /**
     * Relationship: Comment belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Comment belongs to parent comment (self-relation)
     */
    public function parent()
    {
        return $this->belongsTo(InsightComment::class, 'parent_id');
    }

    /**
     * Relationship: Comment has many replies
     */
    public function replies()
    {
        return $this->hasMany(InsightComment::class, 'parent_id')->with('user');
    }

    /**
     * Relationship: Comment has many mentioned users
     * 
     * ✅ FIXED: Only use created_at (no updated_at in pivot table)
     */
    public function mentionedUsers()
    {
        return $this->belongsToMany(
            User::class,
            'insight_comment_mentions',
            'comment_id',
            'mentioned_user_id'
        )->withPivot('created_at'); // ✅ Only created_at, no updated_at
    }

    /**
     * Relationship: Comment has many media files
     */
    public function media()
    {
        return $this->hasMany(InsightCommentMedia::class, 'comment_id')->orderBy('order');
    }

    /**
     * Relationship: Get only images
     */
    public function images()
    {
        return $this->hasMany(InsightCommentMedia::class, 'comment_id')
                    ->where('type', 'image')
                    ->orderBy('order');
    }

    /**
     * Relationship: Get only videos
     */
    public function videos()
    {
        return $this->hasMany(InsightCommentMedia::class, 'comment_id')
                    ->where('type', 'video')
                    ->orderBy('order');
    }

    /**
     * Relationship: Get only files
     */
    public function files()
    {
        return $this->hasMany(InsightCommentMedia::class, 'comment_id')
                    ->where('type', 'file')
                    ->orderBy('order');
    }

    /**
     * Check if this is a parent comment
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a reply
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Scope: Get only parent comments
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Get only replies
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }
}