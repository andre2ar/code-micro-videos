<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use HasFactory, SoftDeletes, Uuid;
    public $incrementing = false;
    protected $keyType = 'string';

    const RATING_LIST = [
        'L', '10', '12', '14', '16', '18'
    ];

    protected $fillable = [
        'title',
        'description',
        'opened',
        'rating',
        'duration',
        'year_launched',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer',
    ];
}
