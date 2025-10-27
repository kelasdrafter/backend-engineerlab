<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleChapter extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    protected $table = 'schedule_chapter';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chapter_id',
        'batch_id',
        'started_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
