<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\SubirFotoCanchaRequest;
use App\Models\Cancha;
use App\Models\CanchaFoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CanchaFotoController extends Controller
{
    public function store(SubirFotoCanchaRequest $request, Cancha $cancha): JsonResponse
    {
        Gate::authorize('gestionar', $cancha);

        $archivo = $request->file('foto');
        $disk = config('filesystems.cancha_photos_disk', 'public');
        $path = $archivo->store("canchas/{$cancha->id}", $disk);

        $foto = CanchaFoto::create([
            'cancha_id' => $cancha->id,
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $archivo->getMimeType(),
            'size_bytes' => $archivo->getSize(),
            'caption' => $request->validated('caption'),
            'estado_verificacion' => 'pendiente',
        ]);

        return response()->json(['data' => $this->presentar($foto)], 201);
    }

    public function destroy(CanchaFoto $foto): JsonResponse
    {
        Gate::authorize('gestionar', $foto->cancha);

        Storage::disk($foto->disk)->delete($foto->path);
        $foto->delete();

        return response()->json(['message' => 'Foto eliminada.']);
    }

    private function presentar(CanchaFoto $foto): array
    {
        return [
            'id' => $foto->id,
            'cancha_id' => $foto->cancha_id,
            'url' => $foto->url,
            'caption' => $foto->caption,
            'estado_verificacion' => $foto->estado_verificacion,
        ];
    }
}