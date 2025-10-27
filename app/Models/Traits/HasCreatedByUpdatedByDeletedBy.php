<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** @mixin Model */
trait HasCreatedByUpdatedByDeletedBy
{
    public function initializeHasCreatedByUpdatedByDeletedBy(): void
    {
        $this->casts['created_by'] = 'json';
        $this->casts['updated_by'] = 'json';
        $this->casts['deleted_by'] = 'json';
    }

    public static function bootHasCreatedByUpdatedByDeletedBy(): void
    {
        static::creating(static function (Model $model) {
            $user = Auth::user();

            if (!$user) {
                return;
            }

            $model->created_by ??= $user;
            $model->updated_by ??= $user;
        });

        static::updating(static function (Model $model) {
            $user = Auth::user();

            if (!$user) {
                return;
            }

            $model->updated_by ??= $user;
        });

        static::deleting(static function (Model $model) {
            $user = Auth::user();

            if (!$user) {
                return;
            }

            $model->deleted_by ??= $user;
        });
    }
}
