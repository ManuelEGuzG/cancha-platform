<?php

namespace App\Models;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CanchaFoto extends Model
{
    protected $fillable = [
        'cancha_id',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'caption',
        'estado_verificacion',
        'observaciones_admin',
        'verificado_por',
        'verificado_en',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'verificado_en' => 'datetime',
    ];

    protected $hidden = ['path', 'disk'];
    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $disk->url($this->path);
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }
}