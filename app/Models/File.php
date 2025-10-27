<?php

namespace App\Models;

use App\Models\Traits\HasCreatedByUpdatedByDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes, HasCreatedByUpdatedByDeletedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'path',
        'mime',
        'size_in_bytes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static function deleteByPath($path): bool
    {
        $file = self::where('path', $path)->first();

        try {
            if (Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }

            if ($file) {
                $file->delete();
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
