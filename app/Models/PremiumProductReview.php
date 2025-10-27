<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PremiumProductReview extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    protected $table = 'premium_product_reviews';

    protected $fillable = [
        'premium_product_id',
        'reviewer_name',
        'reviewer_photo',
        'review_text',
        'is_published',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'created_by' => 'array',
        'updated_by' => 'array',
        'deleted_by' => 'array',
    ];

    /**
     * Get the product that owns the review
     */
    public function product()
    {
        return $this->belongsTo(PremiumProduct::class, 'premium_product_id');
    }

    /**
     * Scope: Only published reviews
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope: Filter by product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('premium_product_id', $productId);
    }

    /**
     * Scope: Search by reviewer name or review text
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('reviewer_name', 'like', "%{$search}%")
              ->orWhere('review_text', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Order by newest
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}