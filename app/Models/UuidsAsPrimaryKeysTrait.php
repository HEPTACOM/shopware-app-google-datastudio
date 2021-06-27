<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Str;

trait UuidsAsPrimaryKeysTrait
{
    public function getIncrementing()
    {
        return false;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }
}
