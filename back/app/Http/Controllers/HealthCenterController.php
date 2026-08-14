<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHealthCenterRequest;
use App\Http\Requests\UpdateHealthCenterRequest;
use App\Models\HealthCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HealthCenterController extends Controller
{
    public function index(): JsonResponse
    {
        $this->ensureCanView();

        return response()->json(
            HealthCenter::query()->orderBy('nombre')->get()
        );
    }

    public function create(): View
    {
        abort(404);
    }

    public function manage(?HealthCenter $healthCenter = null): View
    {
        $this->ensureCanView();

        return view('catalogos.health-centers', [
            'healthCenters' => HealthCenter::orderBy('nombre')->get(),
            'healthCenter' => $healthCenter,
        ]);
    }

    public function store(StoreHealthCenterRequest $request): JsonResponse|RedirectResponse
    {
        $this->ensureCanManage(43);

        $healthCenter = HealthCenter::create([
            'nombre' => mb_strtoupper(trim($request->validated()['nombre'])),
            'direccion' => $this->normalizeOptionalText($request->validated()['direccion'] ?? null),
            'telefono' => $this->normalizeOptionalText($request->validated()['telefono'] ?? null),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Centro de salud registrado.',
                'data' => $healthCenter,
            ], 201);
        }

        return redirect()->route('catalogos.health-centers')->with('status', 'Centro de salud registrado.');
    }

    public function show(HealthCenter $healthCenter): JsonResponse
    {
        $this->ensureCanView();

        return response()->json($healthCenter);
    }

    public function edit(HealthCenter $healthCenter): View
    {
        abort(404);
    }

    public function update(UpdateHealthCenterRequest $request, HealthCenter $healthCenter): JsonResponse|RedirectResponse
    {
        $this->ensureCanManage(44);

        $healthCenter->update([
            'nombre' => mb_strtoupper(trim($request->validated()['nombre'])),
            'direccion' => $this->normalizeOptionalText($request->validated()['direccion'] ?? null),
            'telefono' => $this->normalizeOptionalText($request->validated()['telefono'] ?? null),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Centro de salud actualizado.',
                'data' => $healthCenter->fresh(),
            ]);
        }

        return redirect()->route('catalogos.health-centers')->with('status', 'Centro de salud actualizado.');
    }

    public function destroy(Request $request, HealthCenter $healthCenter): JsonResponse|RedirectResponse
    {
        $this->ensureCanManage(45);

        $healthCenter->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Centro de salud eliminado.',
            ]);
        }

        return redirect()->route('catalogos.health-centers')->with('status', 'Centro de salud eliminado.');
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value);
    }

    private function ensureCanView(): void
    {
        $this->ensurePermission(42, 'No tiene permisos para ver centros de salud.');
    }

    private function ensureCanManage(int $permissionId): void
    {
        $messages = [
            43 => 'No tiene permisos para registrar centros de salud.',
            44 => 'No tiene permisos para modificar centros de salud.',
            45 => 'No tiene permisos para eliminar centros de salud.',
        ];

        $this->ensurePermission($permissionId, $messages[$permissionId] ?? 'No tiene permisos para administrar centros de salud.');
    }

    private function ensurePermission(int $permissionId, string $message): void
    {
        $user = Auth::user();
        $user?->loadMissing('rol.permisos');
        $hasPermission = $user?->rol?->permisos?->contains('id', $permissionId) ?? false;

        if (!$hasPermission) {
            throw ValidationException::withMessages([
                'permission' => $message,
            ]);
        }
    }
}
