<?php


namespace App\Models\Traits;


use Illuminate\Support\Str;

trait Uuid
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($object) {
            $object->id = Str::orderedUuid()->toString();
        });
    }
}
