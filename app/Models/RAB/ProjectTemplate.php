<?php

namespace App\Models\RAB;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'region_id',
        'ahsp_source_id',
        'is_global',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the region
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the AHSP source
     */
    public function ahspSource(): BelongsTo
    {
        return $this->belongsTo(AhspSource::class);
    }

    /**
     * Get all categories in this template
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ProjectTemplateCategory::class, 'template_id');
    }

    /**
     * Get all root categories (no parent)
     */
    public function rootCategories(): HasMany
    {
        return $this->categories()->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Get all projects using this template
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'template_id');
    }

    /**
     * Scope: Only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Global templates only (admin)
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope: User private templates
     */
    public function scopePrivate($query)
    {
        return $query->where('is_global', false);
    }

    /**
     * Scope: Filter by AHSP source
     */
    public function scopeBySource($query, $sourceId)
    {
        return $query->where('ahsp_source_id', $sourceId);
    }

    /**
     * Scope: Filter by region
     */
    public function scopeByRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope: Filter by created user (multi-tenant)
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: Accessible by user (global OR owned by user)
     */
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_global', true)
                ->orWhere('created_by', $userId);
        });
    }

    /**
     * Scope: With all relationships
     */
    public function scopeWithRelations($query)
    {
        return $query->with([
            'region',
            'ahspSource',
            'categories.items.masterAhsp',
        ]);
    }

    /**
     * Check if user can use this template
     */
    public function canBeUsedBy($userId): bool
    {
        return $this->is_global || $this->created_by == $userId;
    }
}
