<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lesson_id',
        'name',
        'file_url',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
