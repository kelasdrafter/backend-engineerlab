<?php

namespace App\Models\RAB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectItemPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'item_id',
        'price',
        'source_type',
        'source_reference',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Scope: Filter by project
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope: Filter by item
     */
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope: System prices only
     */
    public function scopeSystem($query)
    {
        return $query->where('source_type', 'system');
    }

    /**
     * Scope: Manual prices only
     */
    public function scopeManual($query)
    {
        return $query->where('source_type', 'manual');
    }

    /**
     * Check if from system
     */
    public function isSystem(): bool
    {
        return $this->source_type === 'system';
    }

    /**
     * Check if manual input
     */
    public function isManual(): bool
    {
        return $this->source_type === 'manual';
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get source type label
     */
    public function getSourceTypeLabelAttribute(): string
    {
        return match($this->source_type) {
            'system' => 'Sistem',
            'manual' => 'Manual',
            default => $this->source_type,
        };
    }
}
