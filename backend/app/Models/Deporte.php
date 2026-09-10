<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deporte extends Model
{
    protected $fillable = ['nombre', 'slug', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function canchas(): HasMany
    {
        return $this->hasMany(Cancha::class);
    }
}