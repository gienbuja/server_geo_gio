<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Location extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'zone_id',
        'visible',
        'latitude',
        'longitude',
        'comment',
        'datetime',
        'manual',
        'user_id',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'manual' => 'boolean',
        'datetime' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
