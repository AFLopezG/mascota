<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaceRequest;
use App\Http\Requests\UpdatePlaceRequest;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlaceController extends Controller
{
    public function index()
    {
        $this->ensureCanView();

        return Place::query()->orderBy('nombre')->get();
    }

    public function create(): View
    {
        return $this->manage();
    }

    public function manage(?Place $place = null): View
    {
        $this->ensureCanView();

        return view('catalogos.places', [
            'places' => Place::orderBy('nombre')->get(),
            'place' => $place,
        ]);
    }

    public function store(StorePlaceRequest $request): JsonResponse|RedirectResponse
    {
        $this->ensureCanManage(37);

        $place = Place::create([
            'nombre' => mb_strtoupper(trim($request->validated()['nombre'])),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lugar registrado.',
                'data' => $place,
            ], 201);
        }

        return redirect()->route('catalogos.places')->with('status', 'Lugar registrado.');
    }

    public function show(Place $place)
    {
        $this->ensureCanView();

        return $place;
    }

    public function edit(Place $place): View
    {
        $this->ensureCanView();

        return $this->manage($place);
    }

    public function update(UpdatePlaceRequest $request, Place $place): JsonResponse|RedirectResponse
    {
        $this->ensureCanManage(38);

        $place->update([
            'nombre' => mb_strtoupper(trim($request->validated()['nombre'])),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lugar actualizado.',
                'data' => $place->fresh(),
            ]);
        }

        return redirect()->route('catalogos.places')->with('status', 'Lugar actualizado.');
    }

    public function destroy(Request $request, Place $place): JsonResponse|RedirectResponse
    {
        $this->ensureCanManage(39);

        $place->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lugar eliminado.',
            ]);
        }

        return redirect()->route('catalogos.places')->with('status', 'Lugar eliminado.');
    }

    private function ensureCanView(): void
    {
        $this->ensurePermission(36, 'No tiene permisos para ver lugares.');
    }

    private function ensureCanManage(int $permissionId): void
    {
        $messages = [
            37 => 'No tiene permisos para registrar lugares.',
            38 => 'No tiene permisos para modificar lugares.',
            39 => 'No tiene permisos para eliminar lugares.',
        ];

        $this->ensurePermission($permissionId, $messages[$permissionId] ?? 'No tiene permisos para administrar lugares.');
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
