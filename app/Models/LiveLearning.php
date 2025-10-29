<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class LiveLearning extends Model
{
    use HasFactory, SoftDeletes, Sluggable;

    /**
     * The table associated with the model.
     */
    protected $table = 'live_learnings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'thumbnail_url',
        'description',
        'schedule',
        'materials',
        'is_paid',
        'price',
        'zoom_link',
        'community_group_link',
        'max_participants',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'materials' => 'array',
        'is_paid' => 'boolean',
        'price' => 'decimal:2',
        'max_participants' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ]
        ];
    }

    /**
     * Relationship: Live Learning has many registrations
     */
    public function registrations()
    {
        return $this->hasMany(LiveLearningRegistration::class, 'live_learning_id');
    }

    /**
     * Relationship: Live Learning belongs to creator (User)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Live Learning belongs to updater (User)
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship: Live Learning belongs to deleter (User)
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope: Filter only published live learnings
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: Filter only free live learnings
     */
    public function scopeFree($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Scope: Filter only paid live learnings
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if registration is still open
     */
    public function isRegistrationOpen(): bool
    {
        // Check if status is published
        if ($this->status !== 'published') {
            return false;
        }

        // Check if max participants reached (if set)
        if ($this->max_participants !== null) {
            $currentRegistrations = $this->registrations()->count();
            if ($currentRegistrations >= $this->max_participants) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get remaining slots
     */
    public function getRemainingSlots(): ?int
    {
        if ($this->max_participants === null) {
            return null; // Unlimited
        }

        $currentRegistrations = $this->registrations()->count();
        return max(0, $this->max_participants - $currentRegistrations);
    }

    /**
     * Check if user already registered (by email)
     */
    public function isEmailRegistered(string $email): bool
    {
        return $this->registrations()
                    ->where('email', $email)
                    ->exists();
    }
}