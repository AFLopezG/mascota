<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVacunaRequest;
use App\Http\Requests\UpdateVacunaRequest;
use App\Models\Mascota;
use App\Models\Vacuna;
use Illuminate\Http\Request;

class VacunaController extends Controller
{
    public function index(Request $request)
    {
        $query = Vacuna::query()->with(['mascota.persona', 'campania']);

        if ($request->filled('mascota_id')) {
            $query->where('mascota_id', $request->integer('mascota_id'));
        }

        return $query->orderByDesc('fecha')->get();
    }

    public function store(StoreVacunaRequest $request)
    {
        $data = $request->validated();

        Mascota::findOrFail($data['mascota_id']);

        $vacuna = new Vacuna();
        $vacuna->fecha = $data['fecha'];
        $vacuna->fecha_prox = $data['fecha_prox'] ?? null;
        $vacuna->tipo = mb_strtoupper(trim($data['tipo']));
        $vacuna->lugar = mb_strtoupper(trim($data['lugar']));
        $vacuna->num_lote = $data['num_lote'] ?? null;
        $vacuna->observacion = $data['observacion'] ?? null;
        $vacuna->mascota_id = $data['mascota_id'];
        $vacuna->campania_id = $data['campania_id'];
        $vacuna->save();

        return response()->json([
            'message' => 'Vacuna registrada.',
            'data' => $vacuna->fresh(['mascota.persona', 'campania']),
        ], 201);
    }

    public function show(Vacuna $vacuna)
    {
        return response()->json([
            'data' => $vacuna->load(['mascota.persona', 'campania']),
        ]);
    }

    public function update(UpdateVacunaRequest $request, Vacuna $vacuna)
    {
        $data = $request->validated();

        $vacuna->fecha = $data['fecha'];
        $vacuna->fecha_prox = $data['fecha_prox'] ?? null;
        $vacuna->tipo = mb_strtoupper(trim($data['tipo']));
        $vacuna->lugar = mb_strtoupper(trim($data['lugar']));
        $vacuna->num_lote = $data['num_lote'] ?? null;
        $vacuna->observacion = $data['observacion'] ?? null;
        $vacuna->mascota_id = $data['mascota_id'];
        $vacuna->campania_id = $data['campania_id'];
        $vacuna->save();

        return response()->json([
            'message' => 'Vacuna actualizada.',
            'data' => $vacuna->fresh(['mascota.persona', 'campania']),
        ]);
    }

    public function destroy(Vacuna $vacuna)
    {
        $vacuna->delete();

        return response()->json([
            'message' => 'Vacuna eliminada.',
        ]);
    }
}
