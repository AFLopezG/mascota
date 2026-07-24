<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index()
    {
        return Categoria::orderBy('nombre')->get();
    }

    public function store(StoreCategoriaRequest $request): JsonResponse|RedirectResponse
    {
        $categoria = Categoria::create([
            'nombre' => mb_strtoupper(trim($request->input('nombre'))),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Categoria registrada.',
                'data' => $categoria,
            ], 201);
        }

        return redirect()->route('catalogos.categorias')->with('status', 'Categoria registrada.');
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria): JsonResponse|RedirectResponse
    {
        $categoria->update([
            'nombre' => mb_strtoupper(trim($request->input('nombre'))),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Categoria actualizada.',
                'data' => $categoria->fresh(),
            ]);
        }

        return redirect()->route('catalogos.categorias')->with('status', 'Categoria actualizada.');
    }

    public function destroy(Request $request, Categoria $categoria): JsonResponse|RedirectResponse
    {
        $categoria->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Categoria eliminada.',
            ]);
        }

        return redirect()->route('catalogos.categorias')->with('status', 'Categoria eliminada.');
    }
}
