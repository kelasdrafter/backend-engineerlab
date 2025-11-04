<?php

namespace App\Models\RAB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectBoqItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_category_id',
        'item_type',
        'project_ahsp_id',
        'code',
        'name',
        'unit',
        'volume',
        'unit_price',
        'total_price',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'volume' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get the project category
     */
    public function projectCategory(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    /**
     * Get the project AHSP (if type = ahsp)
     */
    public function projectAhsp(): BelongsTo
    {
        return $this->belongsTo(ProjectAhsp::class);
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
     * Scope: Filter by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('project_category_id', $categoryId);
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
     * Recalculate and update total price
     */
    public function recalculateTotal()
    {
        $this->total_price = $this->volume * $this->unit_price;
        $this->save();

        return $this->total_price;
    }

    /**
     * Update unit price from project AHSP calculation
     */
    public function updateUnitPriceFromAhsp()
    {
        if ($this->isAhsp() && $this->projectAhsp) {
            $this->unit_price = $this->projectAhsp->calculateUnitPrice();
            $this->save();

            return $this->unit_price;
        }

        return null;
    }

    /**
     * Get formatted volume
     */
    public function getFormattedVolumeAttribute(): string
    {
        return number_format($this->volume, 2, ',', '.');
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    /**
     * Boot method - Auto calculate total on saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->total_price = $model->volume * $model->unit_price;
        });
    }
}
