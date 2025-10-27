<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'batch_id',
        'course_id',
        'user_name',
        'code',
        'file_url',
        'thumbnail_url',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->code)) {
                $certificate->code = 'KelasDrafter-' . strtoupper(Str::random(10));
            }
        });
    }

    public function setCodeAttribute($value)
    {
        if ($this->exists && $this->code != $value) {
            // Jika record sudah ada, jangan izinkan perubahan `code`.
            return;
        }

        $this->attributes['code'] = $value;
    }


}
