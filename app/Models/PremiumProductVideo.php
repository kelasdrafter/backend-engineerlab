<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PremiumProductVideo extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    protected $table = 'premium_product_videos';

    protected $fillable = [
        'premium_product_id',
        'video_url',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'created_by' => 'array',
        'updated_by' => 'array',
        'deleted_by' => 'array',
    ];

    protected $appends = [
        'youtube_video_id',
        'embed_url',
    ];

    /**
     * Get the product that owns the video
     */
    public function product()
    {
        return $this->belongsTo(PremiumProduct::class, 'premium_product_id');
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
     * Scope: Filter by product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('premium_product_id', $productId);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}