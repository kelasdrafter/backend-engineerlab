<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveLearningRegistration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'live_learning_registrations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'live_learning_id',
        'name',
        'email',
        'whatsapp',
        'registered_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'registered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Registration belongs to Live Learning
     */
    public function liveLearning()
    {
        return $this->belongsTo(LiveLearning::class, 'live_learning_id');
    }

    /**
     * Scope: Filter by live learning ID
     */
    public function scopeLiveLearning($query, $liveLearningId)
    {
        return $query->where('live_learning_id', $liveLearningId);
    }

    /**
     * Scope: Search by name or email
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('whatsapp', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('registered_at', [$startDate, $endDate]);
    }

    /**
     * Format WhatsApp number for display
     */
    public function getFormattedWhatsappAttribute(): string
    {
        $number = $this->whatsapp;
        
        // Remove any non-digit characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Format: 0812-3456-7890
        if (strlen($number) >= 10) {
            return substr($number, 0, 4) . '-' . 
                   substr($number, 4, 4) . '-' . 
                   substr($number, 8);
        }
        
        return $this->whatsapp;
    }

    /**
     * Get WhatsApp link (for click-to-chat)
     */
    public function getWhatsappLinkAttribute(): string
    {
        $number = preg_replace('/[^0-9]/', '', $this->whatsapp);
        
        // Convert to international format
        if (substr($number, 0, 1) === '0') {
            $number = '62' . substr($number, 1);
        }
        
        return "https://wa.me/{$number}";
    }
}