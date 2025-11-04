<?php

namespace App\Models\RAB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplateItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_category_id',
        'item_type',
        'master_ahsp_id',
        'code',
        'name',
        'unit',
        'default_volume',
        'sort_order',
    ];

    protected $casts = [
        'default_volume' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    /**
     * Get the template category
     */
    public function templateCategory(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplateCategory::class, 'template_category_id');
    }

    /**
     * Get the master AHSP (if type = ahsp)
     */
    public function masterAhsp(): BelongsTo
    {
        return $this->belongsTo(MasterAhsp::class);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope: AHSP items only
     */
    public function scopeAhsp($query)
    {
        return $query->where('item_type', 'ahsp');
    }

    /**
     * Scope: Custom items only
     */
    public function scopeCustom($query)
    {
        return $query->where('item_type', 'custom');
    }

    /**
     * Check if this is AHSP item
     */
    public function isAhsp(): bool
    {
        return $this->item_type === 'ahsp';
    }

    /**
     * Check if this is custom item
     */
    public function isCustom(): bool
    {
        return $this->item_type === 'custom';
    }

    /**
     * Get item name (from master AHSP or custom)
     */
    public function getItemNameAttribute(): string
    {
        if ($this->isAhsp() && $this->masterAhsp) {
            return $this->masterAhsp->name;
        }

        return $this->name ?? '';
    }

    /**
     * Get item code (from master AHSP or custom)
     */
    public function getItemCodeAttribute(): string
    {
        if ($this->isAhsp() && $this->masterAhsp) {
            return $this->masterAhsp->code;
        }

        return $this->code ?? '';
    }

    /**
     * Get item unit (from master AHSP or custom)
     */
    public function getItemUnitAttribute(): string
    {
        if ($this->isAhsp() && $this->masterAhsp) {
            return $this->masterAhsp->unit;
        }

        return $this->unit ?? '';
    }
}
