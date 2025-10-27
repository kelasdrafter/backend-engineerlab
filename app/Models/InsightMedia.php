<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InsightMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'insight_id',
        'type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'order',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Relationship: Media belongs to insight
     */
    public function insight()
    {
        return $this->belongsTo(Insight::class);
    }

    /**
     * Get full S3 URL
     */
    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->file_path);
    }

    /**
     * ❌ REMOVED: booted() method
     * 
     * S3 file deletion is now handled by InsightMediaObserver->deleted()
     * This follows the Observer pattern for better separation of concerns.
     * 
     * The logic has been moved to:
     * app/Observers/InsightMediaObserver.php
     */

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