<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDenunciaTipoRequest;
use App\Http\Requests\UpdateDenunciaTipoRequest;
use App\Models\DenunciaTipo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DenunciaTipoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(DenunciaTipo::orderBy('nombre')->get());
    }

    public function store(StoreDenunciaTipoRequest $request): JsonResponse
    {
        $denunciaTipo = DenunciaTipo::create($request->validated());

        return response()->json([
            'message' => 'Tipo de denuncia registrado.',
            'data' => $denunciaTipo,
        ], 201);
    }

    public function show(DenunciaTipo $denunciaTipo): JsonResponse
    {
        return response()->json([
            'data' => $denunciaTipo,
        ]);
    }

    public function update(UpdateDenunciaTipoRequest $request, DenunciaTipo $denunciaTipo): JsonResponse
    {
        $denunciaTipo->update($request->validated());

        return response()->json([
            'message' => 'Tipo de denuncia actualizado.',
            'data' => $denunciaTipo->fresh(),
        ]);
    }

    public function destroy(DenunciaTipo $denunciaTipo): JsonResponse
    {
        $denunciaTipo->delete();

        return response()->json([
            'message' => 'Tipo de denuncia eliminado.',
        ]);
    }
}
