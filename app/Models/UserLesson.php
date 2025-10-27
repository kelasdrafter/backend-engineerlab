<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLesson extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'lesson_id',
        'course_id',
        'is_done',
        "is_markable",
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

}
