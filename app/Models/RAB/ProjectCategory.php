<?php

namespace App\Models\RAB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'code',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'parent_id');
    }

    /**
     * Get all child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProjectCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all BOQ items in this category
     */
    public function boqItems(): HasMany
    {
        return $this->hasMany(ProjectBoqItem::class, 'project_category_id')->orderBy('sort_order');
    }

    /**
     * Scope: Root categories only
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope: With nested children
     */
    public function scopeWithChildren($query)
    {
        return $query->with(['children' => function ($q) {
            $q->orderBy('sort_order');
        }]);
    }

    /**
     * Scope: Filter by project
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Check if this is root category
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Get full path name (Parent > Child > Grandchild)
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            array_unshift($path, $current->name);
        }

        return implode(' > ', $path);
    }

    /**
     * Calculate total price of all items in this category
     */
    public function calculateTotal()
    {
        return $this->boqItems()->sum('total_price');
    }

    /**
     * Calculate total including children categories
     */
    public function calculateTotalWithChildren()
    {
        $total = $this->calculateTotal();

        foreach ($this->children as $child) {
            $total += $child->calculateTotalWithChildren();
        }

        return $total;
    }
}
