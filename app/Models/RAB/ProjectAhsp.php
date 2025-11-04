<?php

namespace App\Models\RAB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectAhsp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_ahsp';

    protected $fillable = [
        'project_id',
        'ahsp_source_id',
        'source_type',
        'master_ahsp_id',
        'code',
        'name',
        'unit',
        'description',
    ];

    /**
     * Get the project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the AHSP source
     */
    public function ahspSource(): BelongsTo
    {
        return $this->belongsTo(AhspSource::class);
    }

    /**
     * Get the master AHSP (if from master)
     */
    public function masterAhsp(): BelongsTo
    {
        return $this->belongsTo(MasterAhsp::class);
    }

    /**
     * Get all composition items
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProjectAhspItem::class);
    }

    /**
     * Get material items only
     */
    public function materialItems(): HasMany
    {
        return $this->items()->where('category', 'material');
    }

    /**
     * Get labor items only
     */
    public function laborItems(): HasMany
    {
        return $this->items()->where('category', 'labor');
    }

    /**
     * Get equipment items only
     */
    public function equipmentItems(): HasMany
    {
        return $this->items()->where('category', 'equipment');
    }

    /**
     * Get all BOQ items using this AHSP
     */
    public function boqItems(): HasMany
    {
        return $this->hasMany(ProjectBoqItem::class, 'project_ahsp_id');
    }

    /**
     * Scope: From master AHSP
     */
    public function scopeFromMaster($query)
    {
        return $query->where('source_type', 'master');
    }

    /**
     * Scope: Custom AHSP
     */
    public function scopeCustom($query)
    {
        return $query->where('source_type', 'custom');
    }

    /**
     * Scope: Filter by project
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope: Filter by AHSP source
     */
    public function scopeBySource($query, $sourceId)
    {
        return $query->where('ahsp_source_id', $sourceId);
    }

    /**
     * Check if from master AHSP
     */
    public function isFromMaster(): bool
    {
        return $this->source_type === 'master';
    }

    /**
     * Check if custom AHSP
     */
    public function isCustom(): bool
    {
        return $this->source_type === 'custom';
    }

    /**
     * Calculate unit price based on project item prices
     */
    public function calculateUnitPrice()
    {
        $totalPrice = 0;

        foreach ($this->items as $ahspItem) {
            $projectItemPrice = ProjectItemPrice::where('project_id', $this->project_id)
                ->where('item_id', $ahspItem->item_id)
                ->first();

            if ($projectItemPrice) {
                $totalPrice += $projectItemPrice->price * $ahspItem->coefficient;
            }
        }

        return $totalPrice;
    }

    /**
     * Get full code with source prefix
     */
    public function getFullCodeAttribute(): string
    {
        return $this->ahspSource->code . '-' . $this->code;
    }
}
