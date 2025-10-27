<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'type',
        'nominal',
        'name',
        'quota',
        'description',
        'thumbnail_url',
        'start_at',
        'end_at',
        'is_public',
        'is_repeatable',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
