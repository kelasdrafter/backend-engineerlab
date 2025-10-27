<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy, Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'privilege',
        'benefit',
        'description',
        'short_description',
        'price',
        'discount_price',
        'total_minutes',
        'category_id',
        'whatsapp_group_url',
        'trailer_url',
        'thumbnail_url',
        'syllabus_url',
        'is_can_checkout',
        'is_active',
        "is_direct_class",
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $appends = ['next_batch'];

    /**
    * Return the sluggable configuration array for this model.
    *
    * @return array
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

    public function category()
    {
        return $this->belongsTo(Enumeration::class, 'category_id');
    }

    /**
     * Mendapatkan semua chapter yang berhubungan dengan course ini.
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    /**
     * Mendapatkan semua lesson melalui chapter yang berhubungan dengan course ini.
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(Benefit::class);
    }

    public function designedFors(): HasMany
    {
        return $this->hasMany(DesignedFor::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function keyPoints(): HasMany
    {
        return $this->hasMany(KeyPoint::class);
    }

    public function privileges(): HasMany
    {
        return $this->hasMany(Privilege::class);
    }

    public function getNextBatchAttribute(){
        $batch = Batch::where('course_id', $this->id)
               ->where('start_date', '>=', now())
               ->orderBy('start_date', 'asc')
               ->first();

        if (!$batch) {
            return null;
        }

        return Carbon::parse($batch->start_date)->format('d M Y');
    }
}
