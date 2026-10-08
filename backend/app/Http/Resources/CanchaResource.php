<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CanchaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'precio_hora' => $this->precio_hora,
            'deporte' => $this->deporte->nombre,
            'fotos' => $this->whenLoaded('fotos', fn () => $this->fotos->where('estado_verificacion', 'aprobada')->map(fn ($foto) => [
                'url' => $foto->url,
                'caption' => $foto->caption,
            ])->values()),
        ];
    }
}