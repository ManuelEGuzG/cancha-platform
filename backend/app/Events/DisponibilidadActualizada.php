<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisponibilidadActualizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $complejoId,
        public readonly int $canchaId,
        public readonly string $fecha,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("complejo.{$this->complejoId}.disponibilidad"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'disponibilidad.actualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'cancha_id' => $this->canchaId,
            'fecha' => $this->fecha,
        ];
    }
}