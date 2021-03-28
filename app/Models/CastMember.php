<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use HasFactory, SoftDeletes, Uuid;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'type',
    ];

    protected $dates = [
        'deleted_at',
    ];

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;
}
