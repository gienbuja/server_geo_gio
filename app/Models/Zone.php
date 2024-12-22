<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Zone extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'description',
        'visible',
        'latitude',
        'longitude',
        'radius',
        'manual',
        'user_id',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'manual' => 'boolean',
        'radius' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
