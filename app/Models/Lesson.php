<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    protected $appends = ['is_done'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chapter_id',
        'name',
        'sequence',
        'embed_url',
        'video_url',
        'description',
        'thumbnail_url',
        'supporting_file_url',
        'is_public',
        'is_active',
        'require_completion',  // ← TAMBAH INI
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function userLessons()
    {
        return $this->hasMany(UserLesson::class);
    }

    public function isDoneByUser($userId)
    {
        return $this->userLessons()->where('user_id', $userId)->exists() ? $this->userLessons()->where('user_id', $userId)->first()->is_done : false;
    }

    public function isMarkableByUser($userId)
    {
        return $this->userLessons()->where('user_id', $userId)->exists() ? $this->userLessons()->where('user_id', $userId)->first()->is_markable : false;
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function getIsDoneAttribute()
    {
        $userId = auth()->id();
        return $this->isDoneByUser($userId);
    }
}