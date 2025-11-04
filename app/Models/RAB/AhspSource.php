<?php

namespace App\Models\RAB;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AhspSource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the user who created this AHSP source
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all master AHSP using this source
     */
    public function masterAhsp(): HasMany
    {
        return $this->hasMany(MasterAhsp::class);
    }

    /**
     * Get all project templates using this source
     */
    public function projectTemplates(): HasMany
    {
        return $this->hasMany(ProjectTemplate::class);
    }

    /**
     * Get all projects using this source
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get all project AHSP using this source
     */
    public function projectAhsp(): HasMany
    {
        return $this->hasMany(ProjectAhsp::class);
    }

    /**
     * Scope: Only active sources
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope: Filter by created user (multi-tenant)
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
