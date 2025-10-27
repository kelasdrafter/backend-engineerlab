<?php
// app/Models/InsightCommentMedia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InsightCommentMedia extends Model
{
    use HasFactory;

    protected $table = 'insight_comment_media';

    protected $fillable = [
        'comment_id',
        'type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Relationship: Media belongs to comment
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(InsightComment::class, 'comment_id');
    }

    /**
     * Get full S3 URL - SAMA SEPERTI InsightMedia
     */
    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->file_path);
    }

    /**
     * Scope: Get media by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Videos only
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope: Images only
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image')->orderBy('order');
    }

    /**
     * Scope: Files only
     */
    public function scopeFiles($query)
    {
        return $query->where('type', 'file');
    }
}