<?php

namespace App\Models\RAB;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id',
        'region_id',
        'price',
        'effective_date',
        'expired_date',
        'is_active',
        'source',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'effective_date' => 'date',
        'expired_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this price
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the region
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Scope: Only active prices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Current valid prices (not expired)
     */
    public function scopeCurrent($query)
    {
        return $query->where('effective_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', now());
            });
    }

    /**
     * Scope: Filter by region
     */
    public function scopeByRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope: Filter by item
     */
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope: Filter by created user (multi-tenant)
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Check if price is currently valid
     */
    public function getIsValidAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->effective_date > now()) {
            return false;
        }

        if ($this->expired_date && $this->expired_date < now()) {
            return false;
        }

        return true;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
