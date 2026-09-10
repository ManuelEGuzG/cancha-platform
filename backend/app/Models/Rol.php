<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = ['nombre'];

    public const PROPIETARIO = 'propietario';
    public const ENCARGADO = 'encargado';
}