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

        $query = RegistroVacuna::query()->with(['campania', 'place', 'healthCenter', 'especie', 'raza', 'user']);

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_vacuna', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_vacuna', '<=', $request->input('fecha_hasta'));
        }

        $query->where('user_id', Auth::id());

        return response()->json([
            'data' => $query->orderByDesc('fecha_vacuna')->orderByDesc('id')->get(),
        ]);
    }

    public function reporte(Request $request): JsonResponse
    {
        $this->ensureCanView($request);

        $data = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
        ]);

        $baseQuery = RegistroVacuna::query()
            ->with(['campania', 'place', 'healthCenter', 'especie', 'raza', 'user'])
            ->when(!empty($data['fecha_desde']), function ($query) use ($data) {
                $query->whereDate('fecha_vacuna', '>=', $data['fecha_desde']);
            })
            ->when(!empty($data['fecha_hasta']), function ($query) use ($data) {
                $query->whereDate('fecha_vacuna', '<=', $data['fecha_hasta']);
            })
            ->where('user_id', Auth::id());

        $rows = $baseQuery
            ->orderByDesc('fecha_vacuna')
            ->orderByDesc('id')
            ->get();
        error_log($rows);
        $resumenEspecies = $rows
            ->groupBy(fn (RegistroVacuna $registro) => is_object($registro->especie) ? $registro->especie->nombre : (string)($registro->especie ?: 'SIN ESPECIE'))
            ->map(function ($items, string $nombre) {
                return [
                    'nombre' => $nombre,
                    'cantidad' => $items->count(),
                ];
            })
            ->values();

        $resumenPlaces = $rows
            ->groupBy(fn (RegistroVacuna $registro) => is_object($registro->place) ? $registro->place->nombre : (string)($registro->place ?: 'SIN LUGAR'))
            ->map(function ($items, string $nombre) {
                return [
                    'nombre' => $nombre,
                    'cantidad' => $items->count(),
                ];
            })
            ->values();

        $resumenHealthCenters = $rows
            ->groupBy(fn (RegistroVacuna $registro) => is_object($registro->healthCenter) ? $registro->healthCenter->nombre : (string)($registro->healthCenter ?: 'SIN CENTRO DE SALUD'))
            ->map(function ($items, string $nombre) {
                return [
                    'nombre' => $nombre,
                    'cantidad' => $items->count(),
                ];
            })
            ->values();

        $resumenMenor = $rows
            ->groupBy(fn (RegistroVacuna $registro) => $registro->menor ? 'SI' : 'NO')
            ->map(function ($items, string $valor) {
                return [
                    'valor' => $valor,
                    'cantidad' => $items->count(),
                ];
            })
            ->values();

        return response()->json([
            'data' => $rows,
            'summary' => [
                'total' => $rows->count(),
                'especies' => $resumenEspecies,
                'places' => $resumenPlaces,
                'health_centers' => $resumenHealthCenters,
                'menor' => $resumenMenor,
            ],
            'filters' => $data,
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

        $user = $request->user();
        $placeId = $user?->place_id;
        $healthCenterId = $user?->health_center_id;

        if ($placeId === null) {
            throw ValidationException::withMessages([
                'place_id' => 'El usuario no tiene un lugar asignado.',
            ]);
        }

        if ($healthCenterId === null) {
            throw ValidationException::withMessages([
                'health_center_id' => 'El usuario no tiene un centro de salud asignado.',
            ]);
        }

        $registro = new RegistroVacuna();
        $registro->cedula = $this->normalizeOptionalText($data['cedula'] ?? null);
        $registro->nombre = $this->normalizeOptionalText($data['nombre'] ?? null);
        $registro->domicilio = $this->normalizeOptionalText($data['domicilio'] ?? null);
        $registro->celular = $this->normalizeOptionalText($data['celular'] ?? null);
        $registro->nombre_mascota = $this->normalizeOptionalText($data['nombre_mascota'] ?? null);
        $registro->menor = (bool) ($data['menor'] ?? false);
        $registro->estado = 'ACTIVO';
        $registro->lat = $this->normalizeOptionalCoordinate($data['lat'] ?? null);
        $registro->lng = $this->normalizeOptionalCoordinate($data['lng'] ?? null);
        $registro->fecha_vacuna = $data['fecha_vacuna'];
        $registro->campania_id = $data['campania_id'];
        $registro->especie_id = $data['especie_id'];
        $registro->raza_id = $data['raza_id'] ?? null;
        $registro->place_id = $placeId;
        $registro->health_center_id = $healthCenterId;
        $registro->user_id = $userId;

        if ($request->hasFile('foto')) {
            $registro->foto = $request->file('foto')->store('registro-vacunas', 'public');
        }

        $registro->save();

        return response()->json([
            'message' => 'Registro de vacuna guardado.',
            'data' => $registro->fresh(['campania', 'place', 'healthCenter', 'especie', 'raza', 'user']),
        ], 201);
    }

    public function show(Request $request, RegistroVacuna $registroVacuna): JsonResponse
    {
        $this->ensureCanView($request);

        return response()->json([
            'data' => $registroVacuna->load(['campania', 'place', 'healthCenter', 'especie', 'raza', 'user']),
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
        $user = $request->user();
        $placeId = $user?->place_id;
        $healthCenterId = $user?->health_center_id;

        if ($placeId === null) {
            throw ValidationException::withMessages([
                'place_id' => 'El usuario no tiene un lugar asignado.',
            ]);
        }

        if ($healthCenterId === null) {
            throw ValidationException::withMessages([
                'health_center_id' => 'El usuario no tiene un centro de salud asignado.',
            ]);
        }

        $registroVacuna->cedula = $this->normalizeOptionalText($data['cedula'] ?? null);
        $registroVacuna->nombre = $this->normalizeOptionalText($data['nombre'] ?? null);
        $registroVacuna->domicilio = $this->normalizeOptionalText($data['domicilio'] ?? null);
        $registroVacuna->celular = $this->normalizeOptionalText($data['celular'] ?? null);
        $registroVacuna->nombre_mascota = $this->normalizeOptionalText($data['nombre_mascota'] ?? null);
        $registroVacuna->especie = $this->normalizeOptionalText($data['especie'] ?? null);
        $registroVacuna->menor = (bool) ($data['menor'] ?? false);
        if ($registroVacuna->isAnulado()) {
            throw ValidationException::withMessages([
                'registro_vacuna' => 'No se puede modificar un registro anulado.',
            ]);
        }
        $registroVacuna->lat = $this->normalizeOptionalCoordinate($data['lat'] ?? null);
        $registroVacuna->lng = $this->normalizeOptionalCoordinate($data['lng'] ?? null);
        $registroVacuna->fecha_vacuna = $data['fecha_vacuna'];
        $registroVacuna->campania_id = $data['campania_id'];
        $registroVacuna->especie_id = $data['especie_id'];
        $registroVacuna->raza_id = $data['raza_id'] ?? null;
        $registroVacuna->place_id = $placeId;
        $registroVacuna->health_center_id = $healthCenterId;

        if ($request->hasFile('foto')) {
            if (!empty($registroVacuna->foto)) {
                Storage::disk('public')->delete($registroVacuna->foto);
            }

            $registroVacuna->foto = $request->file('foto')->store('registro-vacunas', 'public');
        }

        $registroVacuna->save();

        return response()->json([
            'message' => 'Registro de vacuna actualizado.',
            'data' => $registroVacuna->fresh(['campania', 'place', 'healthCenter', 'especie', 'raza', 'user']),
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
            'data' => $registroVacuna->fresh(['campania', 'place', 'healthCenter', 'especie', 'raza', 'user']),
        ]);
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value);
    }

    private function normalizeOptionalCoordinate(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
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
