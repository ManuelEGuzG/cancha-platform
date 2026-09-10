<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplejoDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'descripcion' => $this->descripcion,
            'logo_url' => $this->logo_url,
            'direccion_texto' => $this->direccion_texto,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'telefono' => $this->telefono,
            'whatsapp_numero' => $this->whatsapp_numero,
            'distrito' => $this->distrito->nombre,
            'canton' => $this->distrito->canton->nombre,
            'provincia' => $this->distrito->canton->provincia->nombre,
            'canchas' => CanchaResource::collection($this->whenLoaded('canchas')),
        ];
    }
}