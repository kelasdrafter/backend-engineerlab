<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class LearnCorner extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Sluggable;

    protected $table = 'learn_corners';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'video_url',
        'thumbnail_url',
        'level', // Admin ketik manual
        'view_count',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'view_count' => 'integer',
        'created_by' => 'array',
        'updated_by' => 'array',
        'deleted_by' => 'array',
    ];

    protected $appends = [
        'youtube_video_id',
        'default_thumbnail_url',
        'final_thumbnail_url',
        'embed_url',
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
     * Extract YouTube Video ID from URL
     * Supports:
     * - https://www.youtube.com/watch?v=VIDEO_ID
     * - https://youtu.be/VIDEO_ID
     * - https://www.youtube.com/embed/VIDEO_ID
     */
    public function getYoutubeVideoIdAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
        
        if (preg_match($pattern, $this->video_url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get default YouTube thumbnail URL
     * Try maxresdefault first, fallback to hqdefault if not available
     */
    public function getDefaultThumbnailUrlAttribute(): ?string
    {
        $videoId = $this->youtube_video_id;
        
        if (!$videoId) {
            return null;
        }

        // Try maxresdefault (1280x720) - best quality
        return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
    }

    /**
     * Get final thumbnail URL (custom or YouTube default)
     * Priority: Custom uploaded thumbnail > YouTube default thumbnail
     */
    public function getFinalThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_url ?: $this->default_thumbnail_url;
    }

    /**
     * Get embed URL for YouTube player
     */
    public function getEmbedUrlAttribute(): ?string
    {
        $videoId = $this->youtube_video_id;
        
        if (!$videoId) {
            return null;
        }

        return "https://www.youtube.com/embed/{$videoId}";
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Scope: Only active videos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by level (exact match or partial match)
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', 'like', "%{$level}%");
    }

    /**
     * Scope: Search by title or description
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('level', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Order by popular (view count)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Scope: Order by newest
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Order by oldest
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}