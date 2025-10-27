<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class PremiumProduct extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy, Sluggable;

    protected $table = 'premium_products';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'price',
        'discount_price',
        'thumbnail_url',
        'file_url',
        'view_count',
        'purchase_count',
        'is_featured',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'view_count' => 'integer',
        'purchase_count' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'created_by' => 'array',
        'updated_by' => 'array',
        'deleted_by' => 'array',
    ];

    protected $appends = [
        'final_price',
        'discount_percentage',
        'has_discount',
    ];

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => true,
                'onUpdate' => true,
            ]
        ];
    }

    /**
     * Get final price (with discount if available)
     */
    public function getFinalPriceAttribute()
    {
        return $this->discount_price > 0 ? $this->discount_price : $this->price;
    }

    /**
     * Check if product has discount
     */
    public function getHasDiscountAttribute()
    {
        return $this->discount_price > 0 && $this->discount_price < $this->price;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->has_discount) {
            return 0;
        }

        return round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    /**
     * Get galleries for this product
     */
    public function galleries()
    {
        return $this->hasMany(PremiumProductGallery::class, 'premium_product_id')
                    ->orderBy('sort_order', 'asc');
    }

    /**
     * Get videos for this product
     */
    public function videos()
    {
        return $this->hasMany(PremiumProductVideo::class, 'premium_product_id')
                    ->orderBy('sort_order', 'asc');
    }

    /**
     * Get compatibilities for this product
     */
    public function compatibilities()
    {
        return $this->hasMany(PremiumProductCompatibility::class, 'premium_product_id')
                    ->orderBy('sort_order', 'asc');
    }

    /**
     * Get Q&As for this product
     */
    public function qnas()
    {
        return $this->hasMany(PremiumProductQna::class, 'premium_product_id')
                    ->orderBy('sort_order', 'asc');
    }

    /**
     * Get reviews for this product
     */
    public function reviews()
    {
        return $this->hasMany(PremiumProductReview::class, 'premium_product_id')
                    ->where('is_published', true);
    }

    /**
     * Get all reviews (including unpublished) for admin
     */
    public function allReviews()
    {
        return $this->hasMany(PremiumProductReview::class, 'premium_product_id');
    }

    /**
     * Get transactions for this product
     */
    public function transactions()
    {
        return $this->hasMany(PremiumTransaction::class, 'premium_product_id');
    }

    /**
     * Get purchases for this product
     */
    public function purchases()
    {
        return $this->hasMany(PremiumPurchase::class, 'premium_product_id');
    }

    /**
     * Increment view count
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Increment purchase count
     */
    public function incrementPurchaseCount()
    {
        $this->increment('purchase_count');
    }

    /**
     * Scope: Only active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Only featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Search by name or description
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Order by popular (purchase count)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('purchase_count', 'desc');
    }

    /**
     * Scope: Order by newest
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Order by price (low to high)
     */
    public function scopePriceLowToHigh($query)
    {
        return $query->orderByRaw('CASE WHEN discount_price > 0 THEN discount_price ELSE price END ASC');
    }

    /**
     * Scope: Order by price (high to low)
     */
    public function scopePriceHighToLow($query)
    {
        return $query->orderByRaw('CASE WHEN discount_price > 0 THEN discount_price ELSE price END DESC');
    }
}