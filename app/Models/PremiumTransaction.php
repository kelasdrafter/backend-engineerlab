<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PremiumTransaction extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    protected $table = 'premium_transactions';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'premium_product_id',
        'voucher_code',
        'status',
        'meta',
        'amount',
        'snap_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'created_by' => 'array',
        'updated_by' => 'array',
        'deleted_by' => 'array',
    ];

    /**
     * Boot method to auto-generate UUID
     */
    public static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the product that was purchased
     */
    public function product()
    {
        return $this->belongsTo(PremiumProduct::class, 'premium_product_id');
    }

    /**
     * Get payments for this transaction
     */
    public function payments()
    {
        return $this->hasMany(PremiumPayment::class, 'premium_transaction_id', 'id');
    }

    /**
     * Get purchase record for this transaction
     */
    public function purchase()
    {
        return $this->hasOne(PremiumPurchase::class, 'premium_transaction_id', 'id');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Only pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Only success transactions
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Only failed transactions
     */
    public function scopeFailure($query)
    {
        return $query->where('status', 'failure');
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('premium_product_id', $productId);
    }

    /**
     * Scope: Filter by voucher code
     */
    public function scopeByVoucher($query, $voucherCode)
    {
        return $query->where('voucher_code', $voucherCode);
    }

    /**
     * Scope: Order by newest
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccess()
    {
        return $this->status === 'success';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed()
    {
        return $this->status === 'failure';
    }
}