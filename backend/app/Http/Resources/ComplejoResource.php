<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplejoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url,
            'direccion_texto' => $this->direccion_texto,
            'distrito' => $this->distrito->nombre,
            'canton' => $this->distrito->canton->nombre,
            'total_canchas' => $this->canchas_count ?? $this->canchas->count(),
            'precio_desde' => $this->canchas->min('precio_hora'),
        ];
    }
}