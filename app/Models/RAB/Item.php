<?php

namespace App\Models\RAB;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'unit',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this item
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all prices for this item
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    /**
     * Get all master AHSP items using this item
     */
    public function masterAhspItems(): HasMany
    {
        return $this->hasMany(MasterAhspItem::class);
    }

    /**
     * Get all project AHSP items using this item
     */
    public function projectAhspItems(): HasMany
    {
        return $this->hasMany(ProjectAhspItem::class);
    }

    /**
     * Get all project item prices
     */
    public function projectItemPrices(): HasMany
    {
        return $this->hasMany(ProjectItemPrice::class);
    }

    /**
     * Scope: Only active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by type (material/labor/equipment)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Material items only
     */
    public function scopeMaterial($query)
    {
        return $query->where('type', 'material');
    }

    /**
     * Scope: Labor items only
     */
    public function scopeLabor($query)
    {
        return $query->where('type', 'labor');
    }

    /**
     * Scope: Equipment items only
     */
    public function scopeEquipment($query)
    {
        return $query->where('type', 'equipment');
    }

    /**
     * Scope: Filter by created user (multi-tenant)
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get active price for specific region
     */
    public function getPriceForRegion($regionId)
    {
        return $this->prices()
            ->where('region_id', $regionId)
            ->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', now());
            })
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'material' => 'Material',
            'labor' => 'Upah',
            'equipment' => 'Alat',
            default => $this->type,
        };
    }
}
