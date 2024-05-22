<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAuthor
{
    /**
     * @return void
     */
    public static function bootHasAuthor(): void
    {
        static::creating(function (Model $model) {
            $model->user_id = $model->user_id ?? Auth::id();
        });

        static::addGlobalScope('author-scope', function (Builder $builder) {
            $builder->where('user_id', Auth::id());
        });
    }
}
