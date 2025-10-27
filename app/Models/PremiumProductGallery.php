<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PremiumProductGallery extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    protected $table = 'premium_product_galleries';

    protected $fillable = [
        'premium_product_id',
        'image_url',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'created_by' => 'array',
        'updated_by' => 'array',
        'deleted_by' => 'array',
    ];

    /**
     * Get the product that owns the gallery
     */
    public function product()
    {
        return $this->belongsTo(PremiumProduct::class, 'premium_product_id');
    }

    /**
     * Scope: Filter by product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('premium_product_id', $productId);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}