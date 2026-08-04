<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaniaRequest;
use App\Http\Requests\UpdateCampaniaRequest;
use App\Models\Campania;
use App\Models\CampaniaTipo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CampaniaController extends Controller
{
    public function index(Request $request)
    {
        return Campania::query()
            ->with('campaniaTipo')
            ->when($request->boolean('vigentes'), function ($query) {
                $query->where('estado', 'ACTIVA')
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('fec_fin')
                            ->orWhereDate('fec_fin', '>=', now()->toDateString());
                    });
            })
            ->orderByDesc('fec_ini')
            ->get();
    }

    public function create(): View
    {
        return $this->manage();
    }

    public function manage(?Campania $campania = null): View
    {
        return view('catalogos.campanias', [
            'campanias' => Campania::with('campaniaTipo')->orderByDesc('fec_ini')->get(),
            'campaniaTipos' => CampaniaTipo::orderBy('nombre')->get(),
            'campania' => $campania,
        ]);
    }

    public function store(StoreCampaniaRequest $request): JsonResponse|RedirectResponse
    {
        $campania = Campania::create($this->mapCampaniaData($request->validated()));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Campania registrada.',
                'data' => $campania->fresh(['campaniaTipo']),
            ], 201);
        }

        return redirect()->route('catalogos.campanias')->with('status', 'Campania registrada.');
    }

    public function edit(Campania $campania): View
    {
        return $this->manage($campania);
    }

    public function update(UpdateCampaniaRequest $request, Campania $campania): JsonResponse|RedirectResponse
    {
        $campania->update($this->mapCampaniaData($request->validated(), $campania));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Campania actualizada.',
                'data' => $campania->fresh(['campaniaTipo']),
            ]);
        }

        return redirect()->route('catalogos.campanias')->with('status', 'Campania actualizada.');
    }

    public function destroy(Request $request, Campania $campania): JsonResponse|RedirectResponse
    {
        $campania->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Campania eliminada.',
            ]);
        }

        return redirect()->route('catalogos.campanias')->with('status', 'Campania eliminada.');
    }

    public function anular(Request $request, Campania $campania): JsonResponse|RedirectResponse
    {
        $campania->update([
            'estado' => 'ANULADA',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Campania anulada.',
                'data' => $campania->fresh(['campaniaTipo']),
            ]);
        }

        return redirect()->route('catalogos.campanias')->with('status', 'Campania anulada.');
    }

    private function mapCampaniaData(array $data, ?Campania $campania = null): array
    {
        $estado = trim((string) ($data['estado'] ?? ($campania?->estado ?? 'ACTIVA')));

        $payload = [
            'nombre' => mb_strtoupper(trim($data['nombre'])),
            'fec_ini' => $data['fec_ini'],
            'fec_fin' => $data['fec_fin'],
            'lugar' => mb_strtoupper(trim($data['lugar'])),
            'descripcion' => $this->normalizeOptionalText($data['descripcion'] ?? null),
            'estado' => $estado === '' ? 'ACTIVA' : mb_strtoupper($estado),
            'campania_tipo_id' => (int) $data['campania_tipo_id'],
        ];

        if ($campania === null) {
            $userId = Auth::id();

            if ($userId === null) {
                throw ValidationException::withMessages([
                    'user_id' => 'Debes iniciar sesion para registrar una campania.',
                ]);
            }

            $payload['user_id'] = $userId;
        }

        return $payload;
    }

    private function ensureEditable(Campania $campania): void
    {
        if (!$campania->isLocked()) {
            return;
        }

        throw ValidationException::withMessages([
            'campania' => 'No se puede modificar una campania finalizada o anulada.',
        ]);
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
