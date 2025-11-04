<?php

namespace App\Models\RAB;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterAhsp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_ahsp';

    protected $fillable = [
        'ahsp_source_id',
        'code',
        'name',
        'unit',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this AHSP
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the AHSP source
     */
    public function ahspSource(): BelongsTo
    {
        return $this->belongsTo(AhspSource::class);
    }

    /**
     * Get all composition items
     */
    public function items(): HasMany
    {
        return $this->hasMany(MasterAhspItem::class);
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
     * Get all template items using this AHSP
     */
    public function templateItems(): HasMany
    {
        return $this->hasMany(ProjectTemplateItem::class);
    }

    /**
     * Get all project AHSP using this master
     */
    public function projectAhsp(): HasMany
    {
        return $this->hasMany(ProjectAhsp::class);
    }

    /**
     * Scope: Only active AHSP
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by AHSP source
     */
    public function scopeBySource($query, $sourceId)
    {
        return $query->where('ahsp_source_id', $sourceId);
    }

    /**
     * Scope: Filter by source code (CK, BM, SDA, etc)
     */
    public function scopeBySourceCode($query, $sourceCode)
    {
        return $query->whereHas('ahspSource', function ($q) use ($sourceCode) {
            $q->where('code', $sourceCode);
        });
    }

    /**
     * Scope: Filter by created user (multi-tenant)
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: With all relationships
     */
    public function scopeWithRelations($query)
    {
        return $query->with([
            'ahspSource',
            'items.item',
        ]);
    }

    /**
     * Calculate unit price for specific region
     */
    public function calculateUnitPrice($regionId)
    {
        $totalPrice = 0;

        foreach ($this->items as $ahspItem) {
            $itemPrice = $ahspItem->item->getPriceForRegion($regionId);
            
            if ($itemPrice) {
                $totalPrice += $itemPrice->price * $ahspItem->coefficient;
            }
        }

        return $totalPrice;
    }

    /**
     * Get full code with source
     */
    public function getFullCodeAttribute(): string
    {
        return $this->ahspSource->code . '-' . $this->code;
    }
}
