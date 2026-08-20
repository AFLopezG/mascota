<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\UpdatePersonalRequest;
use App\Models\Personal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PersonalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensurePermission($request, 47, 'No tiene permisos para ver personal.');

        $query = Personal::query()->orderBy('nombre');

        if ($request->filled('q')) {
            $term = mb_strtoupper(trim((string) $request->input('q')));
            $query->where(function ($subQuery) use ($term) {
                $subQuery->whereRaw('UPPER(cedula) LIKE ?', ['%' . $term . '%'])
                    ->orWhereRaw('UPPER(nombre) LIKE ?', ['%' . $term . '%'])
                    ->orWhereRaw('UPPER(celular) LIKE ?', ['%' . $term . '%']);
            });
        }

        if ($request->boolean('limit')) {
            $query->limit(20);
        }

        return response()->json($query->get());
    }

    public function store(StorePersonalRequest $request): JsonResponse
    {
        $this->ensurePermission($request, 48, 'No tiene permisos para registrar personal.');

        $personal = Personal::create($this->normalizePayload($request->validated()));

        return response()->json([
            'message' => 'Personal registrado.',
            'data' => $personal,
        ], 201);
    }

    public function show(Request $request, Personal $personal): JsonResponse
    {
        $this->ensurePermission($request, 47, 'No tiene permisos para ver personal.');

        return response()->json([
            'data' => $personal,
        ]);
    }

    public function update(UpdatePersonalRequest $request, Personal $personal): JsonResponse
    {
        $this->ensurePermission($request, 49, 'No tiene permisos para modificar personal.');

        $personal->update($this->normalizePayload($request->validated()));

        return response()->json([
            'message' => 'Personal actualizado.',
            'data' => $personal->fresh(),
        ]);
    }

    public function destroy(Request $request, Personal $personal): JsonResponse
    {
        $this->ensurePermission($request, 50, 'No tiene permisos para eliminar personal.');

        if ($personal->logs()->exists()) {
            throw ValidationException::withMessages([
                'personal' => 'No se puede eliminar un personal con logs asociados.',
            ]);
        }

        $personal->delete();

        return response()->json([
            'message' => 'Personal eliminado.',
        ]);
    }

    private function normalizePayload(array $data): array
    {
        return [
            'cedula' => mb_strtoupper(trim((string) $data['cedula'])),
            'nombre' => mb_strtoupper(trim((string) $data['nombre'])),
            'celular' => $this->normalizeOptionalText($data['celular'] ?? null),
        ];
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value);
    }

    private function ensurePermission(Request $request, int $permissionId, string $message): void
    {
        $user = $request->user();
        $user?->loadMissing('rol.permisos');
        $hasPermission = $user?->rol?->permisos?->contains('id', $permissionId) ?? false;

        if (!$hasPermission) {
            throw ValidationException::withMessages([
                'permission' => $message,
            ]);
        }
    }
}
