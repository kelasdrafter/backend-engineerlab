<?php

namespace App\Models\RAB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterAhspItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'master_ahsp_id',
        'category',
        'item_id',
        'coefficient',
        'sort_order',
    ];

    protected $casts = [
        'coefficient' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    /**
     * Get the master AHSP
     */
    public function masterAhsp(): BelongsTo
    {
        return $this->belongsTo(MasterAhsp::class);
    }

    /**
     * Get the item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Material items only
     */
    public function scopeMaterial($query)
    {
        return $query->where('category', 'material');
    }

    /**
     * Scope: Labor items only
     */
    public function scopeLabor($query)
    {
        return $query->where('category', 'labor');
    }

    /**
     * Scope: Equipment items only
     */
    public function scopeEquipment($query)
    {
        return $query->where('category', 'equipment');
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'material' => 'Bahan',
            'labor' => 'Upah',
            'equipment' => 'Alat',
            default => $this->category,
        };
    }

    /**
     * Calculate price for specific region
     */
    public function calculatePrice($regionId)
    {
        $itemPrice = $this->item->getPriceForRegion($regionId);
        
        if (!$itemPrice) {
            return 0;
        }

        return $itemPrice->price * $this->coefficient;
    }
}
