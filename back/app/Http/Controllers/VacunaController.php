<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Vacuna;
use Illuminate\Http\Request;

class VacunaController extends Controller
{
    public function index(Request $request)
    {
        $query = Vacuna::query()->with(['mascota.persona']);

        if ($request->filled('mascota_id')) {
            $query->where('mascota_id', $request->integer('mascota_id'));
        }

        return $query->orderByDesc('fecha')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mascota_id' => ['required', 'integer', 'exists:mascotas,id'],
            'fecha' => ['required', 'date'],
            'tipo' => ['required', 'string'],
            'lugar' => ['required', 'string'],
            'observacion' => ['nullable', 'string'],
        ]);

        Mascota::findOrFail($data['mascota_id']);

        $vacuna = new Vacuna();
        $vacuna->fecha = $data['fecha'];
        $vacuna->tipo = mb_strtoupper(trim($data['tipo']));
        $vacuna->lugar = mb_strtoupper(trim($data['lugar']));
        $vacuna->observacion = $data['observacion'] ?? null;
        $vacuna->mascota_id = $data['mascota_id'];
        $vacuna->save();

        return response()->json([
            'message' => 'Vacuna registrada.',
            'data' => $vacuna->fresh(['mascota.persona']),
        ], 201);
    }

    public function show(Vacuna $vacuna)
    {
        return response()->json([
            'data' => $vacuna->load(['mascota.persona']),
        ]);
    }

    public function update(Request $request, Vacuna $vacuna)
    {
        $data = $request->validate([
            'mascota_id' => ['required', 'integer', 'exists:mascotas,id'],
            'fecha' => ['required', 'date'],
            'tipo' => ['required', 'string'],
            'lugar' => ['required', 'string'],
            'observacion' => ['nullable', 'string'],
        ]);

        $vacuna->fecha = $data['fecha'];
        $vacuna->tipo = mb_strtoupper(trim($data['tipo']));
        $vacuna->lugar = mb_strtoupper(trim($data['lugar']));
        $vacuna->observacion = $data['observacion'] ?? null;
        $vacuna->mascota_id = $data['mascota_id'];
        $vacuna->save();

        return response()->json([
            'message' => 'Vacuna actualizada.',
            'data' => $vacuna->fresh(['mascota.persona']),
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
