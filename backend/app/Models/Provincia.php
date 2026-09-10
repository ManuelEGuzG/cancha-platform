<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provincia extends Model
{
    protected $fillable = ['pais_id', 'nombre'];

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    public function cantones(): HasMany
    {
        return $this->hasMany(Canton::class);
    }
}