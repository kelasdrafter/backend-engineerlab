<?php

namespace App\Models\RAB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplateCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_id',
        'parent_id',
        'name',
        'code',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'template_id');
    }

    /**
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplateCategory::class, 'parent_id');
    }

    /**
     * Get all child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProjectTemplateCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all items in this category
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProjectTemplateItem::class, 'template_category_id')->orderBy('sort_order');
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
}
