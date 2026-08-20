<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProcesoController extends Controller
{
    private const COLORS = [
        'primary',
        'secondary',
        'accent',
        'positive',
        'negative',
        'warning',
        'info',
        'dark',
        'grey-7',
        'indigo',
        'teal',
        'amber',
        'orange',
        'deep-orange',
        'purple',
        'pink',
    ];

    public function index(): JsonResponse
    {
        return response()->json(Proceso::orderBy('orden')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);

        return DB::transaction(function () use ($data) {
            $orden = $this->normalizeOrderForCreate((int) $data['orden']);
            $this->shiftOrdersForInsert($orden);

            $proceso = Proceso::create([
                'orden' => $orden,
                'descripcion' => $this->normalizeText($data['descripcion']),
                'color' => $data['color'],
            ]);

            return response()->json([
                'message' => 'Proceso registrado.',
                'data' => $proceso,
            ], 201);
        });
    }

    public function show(Proceso $proceso): JsonResponse
    {
        return response()->json([
            'data' => $proceso,
        ]);
    }

    public function update(Request $request, Proceso $proceso): JsonResponse
    {
        $data = $this->validatePayload($request, $proceso->id);

        return DB::transaction(function () use ($data, $proceso) {
            $newOrder = $this->normalizeOrderForUpdate((int) $data['orden']);
            $oldOrder = (int) $proceso->orden;

            if ($newOrder > $oldOrder) {
                Proceso::query()
                    ->where('id', '!=', $proceso->id)
                    ->whereBetween('orden', [$oldOrder + 1, $newOrder])
                    ->decrement('orden');
            } elseif ($newOrder < $oldOrder) {
                Proceso::query()
                    ->where('id', '!=', $proceso->id)
                    ->whereBetween('orden', [$newOrder, $oldOrder - 1])
                    ->increment('orden');
            }

            $proceso->update([
                'orden' => $newOrder,
                'descripcion' => $this->normalizeText($data['descripcion']),
                'color' => $data['color'],
            ]);

            return response()->json([
                'message' => 'Proceso actualizado.',
                'data' => $proceso->fresh(),
            ]);
        });
    }

    public function destroy(Proceso $proceso): JsonResponse
    {
        if ($proceso->logs()->exists()) {
            throw ValidationException::withMessages([
                'id' => 'No se puede eliminar un proceso con logs registrados.',
            ]);
        }

        $order = (int) $proceso->orden;

        return DB::transaction(function () use ($proceso, $order) {
            $proceso->delete();

            Proceso::query()
                ->where('orden', '>', $order)
                ->decrement('orden');

            return response()->json([
                'message' => 'Proceso eliminado.',
            ]);
        });
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $maxOrder = max(1, (int) Proceso::count() + ($ignoreId ? 0 : 1));

        return $request->validate([
            'orden' => ['required', 'integer', 'min:1', 'max:' . $maxOrder],
            'descripcion' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', Rule::in(self::COLORS)],
        ]);
    }

    private function normalizeOrderForCreate(int $order): int
    {
        $maxOrder = (int) Proceso::count() + 1;

        return max(1, min($order, $maxOrder));
    }

    private function normalizeOrderForUpdate(int $order): int
    {
        $maxOrder = max(1, (int) Proceso::count());

        return max(1, min($order, $maxOrder));
    }

    private function shiftOrdersForInsert(int $order): void
    {
        Proceso::query()
            ->where('orden', '>=', $order)
            ->increment('orden');
    }

    private function normalizeText(string $value): string
    {
        return mb_strtoupper(trim($value));
    }
}
