<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRazaRequest;
use App\Http\Requests\UpdateRazaRequest;
use App\Models\Especie;
use App\Models\Raza;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RazaController extends Controller
{
    public function index()
    {
        return Raza::with('especie')->orderBy('nombre')->get();
    }

    public function manage(?Raza $raza = null): View
    {
        return view('catalogos.razas', [
            'razas' => Raza::with('especie')->orderBy('nombre')->get(),
            'raza' => $raza,
            'especies' => Especie::orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreRazaRequest $request): JsonResponse|RedirectResponse
    {
        $raza = Raza::create([
            'nombre' => mb_strtoupper(trim($request->input('nombre'))),
            'descrpcion' => $request->filled('descrpcion') ? mb_strtoupper(trim($request->input('descrpcion'))) : null,
            'especie_id' => $request->integer('especie_id'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Raza registrada.',
                'data' => $raza->load('especie'),
            ], 201);
        }

        return redirect()->route('catalogos.razas')->with('status', 'Raza registrada.');
    }

    public function edit(Raza $raza): View
    {
        return $this->manage($raza);
    }

    public function update(UpdateRazaRequest $request, Raza $raza): JsonResponse|RedirectResponse
    {
        $raza->update([
            'nombre' => mb_strtoupper(trim($request->input('nombre'))),
            'descrpcion' => $request->filled('descrpcion') ? mb_strtoupper(trim($request->input('descrpcion'))) : null,
            'especie_id' => $request->integer('especie_id'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Raza actualizada.',
                'data' => $raza->fresh()->load('especie'),
            ]);
        }

        return redirect()->route('catalogos.razas')->with('status', 'Raza actualizada.');
    }

    public function destroy(Request $request, Raza $raza): JsonResponse|RedirectResponse
    {
        $raza->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Raza eliminada.',
            ]);
        }

        return redirect()->route('catalogos.razas')->with('status', 'Raza eliminada.');
    }
}
