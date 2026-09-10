<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
    protected $table = 'paises';

    protected $fillable = ['nombre', 'codigo_iso'];

    public function provincias(): HasMany
    {
        return $this->hasMany(Provincia::class);
    }
}