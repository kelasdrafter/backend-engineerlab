<?php

namespace App\Models\RAB;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'province',
        'city',
        'type',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this region
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all item prices in this region
     */
    public function itemPrices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    /**
     * Get all project templates in this region
     */
    public function projectTemplates(): HasMany
    {
        return $this->hasMany(ProjectTemplate::class);
    }

    /**
     * Get all projects in this region
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Scope: Only active regions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by province
     */
    public function scopeByProvince($query, $province)
    {
        return $query->where('province', $province);
    }

    /**
     * Scope: Filter by type (city/regency)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by created user (multi-tenant)
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get full name (province - city)
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->province} - {$this->city}";
    }
}
