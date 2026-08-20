<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistroVacunaRequest;
use App\Http\Requests\UpdateRegistroVacunaRequest;
use App\Models\RegistroVacuna;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $payload = $this->buildReportPayload($request);

        return response()->json([
            'data' => $payload['rows'],
            'summary' => $payload['summary'],
            'filters' => $payload['filters'],
        ]);
    }

    public function reportePdf(Request $request)
    {
        $payload = $this->buildReportPayload($request);

        $pdf = Pdf::loadView('pdf.reporte-registro-vacunas', $payload)
            ->setPaper('a4', 'landscape');

        $filename = sprintf(
            'reporte-vacunas-%s-a-%s.pdf',
            str_replace('-', '', (string) ($payload['filters']['fecha_desde'] ?? 'inicio')),
            str_replace('-', '', (string) ($payload['filters']['fecha_hasta'] ?? 'fin'))
        );

        return $pdf->download($filename);
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

    private function ensureCanReport(Request $request): void
    {
        $user = $request->user();
        $user?->loadMissing('rol.permisos');
        $hasPermission = $user?->rol?->permisos?->contains('id', 46) ?? false;

        if (!$hasPermission) {
            throw ValidationException::withMessages([
                'permission' => 'No tiene permisos para ver el reporte de vacunas.',
            ]);
        }
    }

    private function buildReportPayload(Request $request): array
    {
        $this->ensureCanReport($request);

        $filters = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
        ]);

        $rows = RegistroVacuna::query()
            ->with(['campania', 'place', 'healthCenter', 'especie', 'raza', 'user'])
            ->when(!empty($filters['fecha_desde']), function ($query) use ($filters) {
                $query->whereDate('fecha_vacuna', '>=', $filters['fecha_desde']);
            })
            ->when(!empty($filters['fecha_hasta']), function ($query) use ($filters) {
                $query->whereDate('fecha_vacuna', '<=', $filters['fecha_hasta']);
            })
            ->where('user_id', Auth::id())
            ->orderByDesc('fecha_vacuna')
            ->orderByDesc('id')
            ->get();

        return [
            'filters' => $filters,
            'rows' => $rows,
            'pdf_rows' => $rows->map(fn (RegistroVacuna $registro) => $this->buildReportRow($registro))->values(),
            'summary' => [
                'total' => $rows->count(),
                'especies' => $this->buildGroupedSummary($rows, fn (RegistroVacuna $registro) => $this->resolveEspecieLabel($registro)),
                'health_centers' => $this->buildGroupedSummary($rows, fn (RegistroVacuna $registro) => $this->resolveHealthCenterLabel($registro)),
                'edad' => [
                    [
                        'valor' => 'Menor de 1 año',
                        'cantidad' => $rows->filter(fn (RegistroVacuna $registro) => (bool) $registro->menor)->count(),
                    ],
                    [
                        'valor' => 'Mayor de 1 año',
                        'cantidad' => $rows->filter(fn (RegistroVacuna $registro) => !((bool) $registro->menor))->count(),
                    ],
                ],
            ],
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
    }

    private function buildGroupedSummary($rows, callable $groupResolver)
    {
        return $rows
            ->groupBy($groupResolver)
            ->map(function ($items, string $nombre) {
                return [
                    'nombre' => $nombre,
                    'cantidad' => $items->count(),
                ];
            })
            ->values();
    }

    private function buildReportRow(RegistroVacuna $registro): array
    {
        return [
            'fecha_vacuna' => $registro->fecha_vacuna,
            'cedula' => $registro->cedula,
            'nombre' => $registro->nombre,
            'nombre_mascota' => $registro->nombre_mascota,
            'especie' => $this->resolveEspecieLabel($registro),
            'place' => $this->resolvePlaceLabel($registro),
            'health_center' => $this->resolveHealthCenterLabel($registro),
            'menor' => (bool) $registro->menor,
            'menor_label' => (bool) $registro->menor ? 'Menor de 1 año' : 'Mayor de 1 año',
        ];
    }

    private function resolveEspecieLabel(RegistroVacuna $registro): string
    {
        return $registro->especie?->nombre
            ?? (is_string($registro->especie) && trim($registro->especie) !== '' ? $registro->especie : 'SIN ESPECIE');
    }

    private function resolvePlaceLabel(RegistroVacuna $registro): string
    {
        return $registro->place?->nombre
            ?? (is_string($registro->place) && trim($registro->place) !== '' ? $registro->place : 'SIN LUGAR');
    }

    private function resolveHealthCenterLabel(RegistroVacuna $registro): string
    {
        return $registro->healthCenter?->nombre
            ?? (is_string($registro->healthCenter) && trim($registro->healthCenter) !== '' ? $registro->healthCenter : 'SIN CENTRO DE SALUD');
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
