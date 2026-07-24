<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEspecieRequest;
use App\Http\Requests\UpdateEspecieRequest;
use App\Models\Especie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EspecieController extends Controller
{
    public function index()
    {
        return Especie::orderBy('nombre')->get();
    }

    public function manage(?Especie $especie = null): View
    {
        return view('catalogos.especies', [
            'especies' => Especie::orderBy('nombre')->get(),
            'especie' => $especie,
        ]);
    }

    public function store(StoreEspecieRequest $request): JsonResponse|RedirectResponse
    {
        $especie = Especie::create([
            'codigo' => mb_strtoupper(trim($request->input('codigo'))),
            'nombre' => mb_strtoupper(trim($request->input('nombre'))),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Especie registrada.',
                'data' => $especie,
            ], 201);
        }

        return redirect()->route('catalogos.especies')->with('status', 'Especie registrada.');
    }

    public function edit(Especie $especie): View
    {
        return $this->manage($especie);
    }

    public function update(UpdateEspecieRequest $request, Especie $especie): JsonResponse|RedirectResponse
    {
        $especie->update([
            'codigo' => mb_strtoupper(trim($request->input('codigo'))),
            'nombre' => mb_strtoupper(trim($request->input('nombre'))),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Especie actualizada.',
                'data' => $especie->fresh(),
            ]);
        }

        return redirect()->route('catalogos.especies')->with('status', 'Especie actualizada.');
    }

    public function destroy(Request $request, Especie $especie): JsonResponse|RedirectResponse
    {
        $especie->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Especie eliminada.',
            ]);
        }

        return redirect()->route('catalogos.especies')->with('status', 'Especie eliminada.');
    }
}
