<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistroVacunaRequest;
use App\Http\Requests\UpdateRegistroVacunaRequest;
use App\Models\RegistroVacuna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RegistroVacunaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureCanView($request);

        $query = RegistroVacuna::query()->with(['campania', 'place', 'especie', 'raza', 'user']);

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_vacuna', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_vacuna', '<=', $request->input('fecha_hasta'));
        }

        return response()->json([
            'data' => $query->orderByDesc('fecha_vacuna')->orderByDesc('id')->get(),
        ]);
    }

    public function create()
    {
        abort(404);
    }

    public function store(StoreRegistroVacunaRequest $request): JsonResponse
    {
        $this->ensureCanCreate($request);

        $data = $request->validated();
        $userId = Auth::id();

        if ($userId === null) {
            throw ValidationException::withMessages([
                'user_id' => 'Debes iniciar sesion para registrar una vacuna.',
            ]);
        }

        $registro = new RegistroVacuna();
        $registro->cedula = $this->normalizeOptionalText($data['cedula'] ?? null);
        $registro->nombre = $this->normalizeOptionalText($data['nombre'] ?? null);
        $registro->domicilio = $this->normalizeOptionalText($data['domicilio'] ?? null);
        $registro->celular = $this->normalizeOptionalText($data['celular'] ?? null);
        $registro->nombre_mascota = $this->normalizeOptionalText($data['nombre_mascota'] ?? null);
        $registro->especie = $this->normalizeOptionalText($data['especie'] ?? null);
        $registro->raza = $this->normalizeOptionalText($data['raza'] ?? null);
        $registro->menor = (bool) ($data['menor'] ?? false);
        $registro->estado = 'ACTIVO';
        $registro->fecha_vacuna = $data['fecha_vacuna'];
        $registro->campania_id = $data['campania_id'];
        $registro->especie_id = $data['especie_id'];
        $registro->raza_id = $data['raza_id'] ?? null;
        $registro->place_id = $data['place_id'];
        $registro->user_id = $userId;

        if ($request->hasFile('foto')) {
            $registro->foto = $request->file('foto')->store('registro-vacunas', 'public');
        }

        $registro->save();

        return response()->json([
            'message' => 'Registro de vacuna guardado.',
            'data' => $registro->fresh(['campania', 'place', 'especie', 'raza', 'user']),
        ], 201);
    }

    public function show(Request $request, RegistroVacuna $registroVacuna): JsonResponse
    {
        $this->ensureCanView($request);

        return response()->json([
            'data' => $registroVacuna->load(['campania', 'place', 'especie', 'raza', 'user']),
        ]);
    }

    public function edit(RegistroVacuna $registroVacuna)
    {
        abort(404);
    }

    public function update(UpdateRegistroVacunaRequest $request, RegistroVacuna $registroVacuna): JsonResponse
    {
        $this->ensureCanCreate($request);

        $data = $request->validated();

        $registroVacuna->cedula = $this->normalizeOptionalText($data['cedula'] ?? null);
        $registroVacuna->nombre = $this->normalizeOptionalText($data['nombre'] ?? null);
        $registroVacuna->domicilio = $this->normalizeOptionalText($data['domicilio'] ?? null);
        $registroVacuna->celular = $this->normalizeOptionalText($data['celular'] ?? null);
        $registroVacuna->nombre_mascota = $this->normalizeOptionalText($data['nombre_mascota'] ?? null);
        $registroVacuna->especie = $this->normalizeOptionalText($data['especie'] ?? null);
        $registroVacuna->raza = $this->normalizeOptionalText($data['raza'] ?? null);
        $registroVacuna->menor = (bool) ($data['menor'] ?? false);
        if ($registroVacuna->isAnulado()) {
            throw ValidationException::withMessages([
                'registro_vacuna' => 'No se puede modificar un registro anulado.',
            ]);
        }
        $registroVacuna->fecha_vacuna = $data['fecha_vacuna'];
        $registroVacuna->campania_id = $data['campania_id'];
        $registroVacuna->especie_id = $data['especie_id'];
        $registroVacuna->raza_id = $data['raza_id'] ?? null;
        $registroVacuna->place_id = $data['place_id'];

        if ($request->hasFile('foto')) {
            if (!empty($registroVacuna->foto)) {
                Storage::disk('public')->delete($registroVacuna->foto);
            }

            $registroVacuna->foto = $request->file('foto')->store('registro-vacunas', 'public');
        }

        $registroVacuna->save();

        return response()->json([
            'message' => 'Registro de vacuna actualizado.',
            'data' => $registroVacuna->fresh(['campania', 'place', 'especie', 'raza', 'user']),
        ]);
    }

    public function destroy(RegistroVacuna $registroVacuna): JsonResponse
    {
        if (!empty($registroVacuna->foto)) {
            Storage::disk('public')->delete($registroVacuna->foto);
        }

        $registroVacuna->delete();

        return response()->json([
            'message' => 'Registro de vacuna eliminado.',
        ]);
    }

    public function anular(Request $request, RegistroVacuna $registroVacuna): JsonResponse
    {
        $this->ensureCanAnular($request);

        if ($registroVacuna->isAnulado()) {
            throw ValidationException::withMessages([
                'registro_vacuna' => 'El registro ya fue anulado.',
            ]);
        }

        $registroVacuna->update([
            'estado' => 'ANULADO',
        ]);

        return response()->json([
            'message' => 'Registro de vacuna anulado.',
            'data' => $registroVacuna->fresh(['campania', 'place', 'especie', 'raza', 'user']),
        ]);
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value);
    }

    private function ensureCanAnular(Request $request): void
    {
        $user = $request->user();
        $user?->loadMissing('rol.permisos');
        $hasPermission = $user?->rol?->permisos?->contains('id', 35) ?? false;

        if (!$hasPermission) {
            throw ValidationException::withMessages([
                'permission' => 'No tiene permisos para anular registros de vacuna.',
            ]);
        }
    }

    private function ensureCanView(Request $request): void
    {
        $user = $request->user();
        $user?->loadMissing('rol.permisos');
        $hasPermission = $user?->rol?->permisos?->contains('id', 40) ?? false;

        if (!$hasPermission) {
            throw ValidationException::withMessages([
                'permission' => 'No tiene permisos para ver registros de vacuna.',
            ]);
        }
    }

    private function ensureCanCreate(Request $request): void
    {
        $user = $request->user();
        $user?->loadMissing('rol.permisos');
        $hasPermission = $user?->rol?->permisos?->contains('id', 41) ?? false;

        if (!$hasPermission) {
            throw ValidationException::withMessages([
                'permission' => 'No tiene permisos para registrar vacunas.',
            ]);
        }
    }

}
