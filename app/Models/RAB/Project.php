<?php

namespace App\Models\RAB;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'region_id',
        'template_id',
        'ahsp_source_id',
        'overhead_percentage',
        'profit_percentage',
        'ppn_percentage',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'overhead_percentage' => 'decimal:2',
        'profit_percentage' => 'decimal:2',
        'ppn_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who owns this project
     */
    public function owner(): BelongsTo
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
     * Get the template (if created from template)
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'template_id');
    }

    /**
     * Get the AHSP source
     */
    public function ahspSource(): BelongsTo
    {
        return $this->belongsTo(AhspSource::class);
    }

    /**
     * Get all categories (BOQ structure)
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ProjectCategory::class);
    }

    /**
     * Get root categories only
     */
    public function rootCategories(): HasMany
    {
        return $this->categories()->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Get all project AHSP
     */
    public function projectAhsp(): HasMany
    {
        return $this->hasMany(ProjectAhsp::class);
    }

    /**
     * Get all item prices snapshot
     */
    public function itemPrices(): HasMany
    {
        return $this->hasMany(ProjectItemPrice::class);
    }

    /**
     * Scope: Only active projects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Draft projects only
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Active projects only (status)
     */
    public function scopeActiveStatus($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Completed projects only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
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
     * Scope: Filter by owner (multi-tenant)
     */
    public function scopeByOwner($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: With all relationships
     */
    public function scopeWithRelations($query)
    {
        return $query->with([
            'region',
            'ahspSource',
            'template',
            'categories.boqItems',
        ]);
    }

    /**
     * Check if created from template
     */
    public function isFromTemplate(): bool
    {
        return !is_null($this->template_id);
    }

    /**
     * Calculate total BOQ price
     */
    public function calculateTotalBoq()
    {
        $total = 0;

        foreach ($this->categories as $category) {
            foreach ($category->boqItems as $item) {
                $total += $item->total_price;
            }
        }

        return $total;
    }

    /**
     * Calculate overhead amount
     */
    public function calculateOverhead()
    {
        return $this->calculateTotalBoq() * ($this->overhead_percentage / 100);
    }

    /**
     * Calculate profit amount
     */
    public function calculateProfit()
    {
        $subtotal = $this->calculateTotalBoq() + $this->calculateOverhead();
        return $subtotal * ($this->profit_percentage / 100);
    }

    /**
     * Calculate subtotal (before tax)
     */
    public function calculateSubtotal()
    {
        return $this->calculateTotalBoq() + $this->calculateOverhead() + $this->calculateProfit();
    }

    /**
     * Calculate PPN amount
     */
    public function calculatePpn()
    {
        return $this->calculateSubtotal() * ($this->ppn_percentage / 100);
    }

    /**
     * Calculate grand total (final price)
     */
    public function calculateGrandTotal()
    {
        return $this->calculateSubtotal() + $this->calculatePpn();
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'active' => 'Aktif',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'active' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
