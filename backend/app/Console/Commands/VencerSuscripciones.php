<?php

namespace App\Console\Commands;

use App\Models\Complejo;
use Illuminate\Console\Command;

class VencerSuscripciones extends Command
{
    protected $signature = 'suscripciones:vencer';
    protected $description = 'Marca como inactivas las suscripciones cuya fecha de vencimiento ya pasó';

    public function handle(): void
    {
        $afectados = Complejo::where('suscripcion_activa', true)
            ->whereNotNull('suscripcion_vence_en')
            ->where('suscripcion_vence_en', '<', now()->toDateString())
            ->update(['suscripcion_activa' => false]);

        $this->info("Suscripciones vencidas actualizadas: {$afectados}");
    }
}