<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaniaTipoRequest;
use App\Http\Requests\UpdateCampaniaTipoRequest;
use App\Models\CampaniaTipo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaniaTipoController extends Controller
{
    public function index()
    {
        return CampaniaTipo::orderBy('nombre')->get();
    }

    public function create()
    {
        return $this->manage();
    }

    public function store(StoreCampaniaTipoRequest $request): JsonResponse|RedirectResponse
    {
        $campaniaTipo = CampaniaTipo::create([
            'nombre' => mb_strtoupper(trim($request->input('nombre'))),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tipo de campaña registrado.',
                'data' => $campaniaTipo,
            ], 201);
        }

        return redirect()->route('catalogos.campania-tipos')->with('status', 'Tipo de campaña registrado.');
    }

    public function show(CampaniaTipo $campaniaTipo)
    {
        return $campaniaTipo;
    }

    public function edit(CampaniaTipo $campaniaTipo)
    {
        return $this->manage($campaniaTipo);
    }

    public function update(UpdateCampaniaTipoRequest $request, CampaniaTipo $campaniaTipo): JsonResponse|RedirectResponse
    {
        $campaniaTipo->update([
            'nombre' => mb_strtoupper(trim($request->input('nombre'))),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tipo de campaña actualizado.',
                'data' => $campaniaTipo->fresh(),
            ]);
        }

        return redirect()->route('catalogos.campania-tipos')->with('status', 'Tipo de campaña actualizado.');
    }

    public function destroy(Request $request, CampaniaTipo $campaniaTipo): JsonResponse|RedirectResponse
    {
        $campaniaTipo->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tipo de campaña eliminado.',
            ]);
        }

        return redirect()->route('catalogos.campania-tipos')->with('status', 'Tipo de campaña eliminado.');
    }

    public function manage(?CampaniaTipo $campaniaTipo = null): View
    {
        return view('catalogos.campania_tipos', [
            'campaniaTipos' => CampaniaTipo::orderBy('nombre')->get(),
            'campaniaTipo' => $campaniaTipo,
        ]);
    }
}
