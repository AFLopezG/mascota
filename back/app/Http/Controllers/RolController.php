<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RolController extends Controller
{
    public function index()
    {
        return Rol::with('permisos')->get();

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:rols,nombre'],
            'permiso_ids' => ['array'],
            'permiso_ids.*' => ['integer', 'exists:permisos,id'],
        ]);

        $rol = Rol::create([
            'nombre' => $data['nombre'],
        ]);

        $rol->permisos()->sync($data['permiso_ids'] ?? []);

        return response()->json([
            'message' => 'Rol registrado.',
            'data' => $this->payload($rol->load('permisos')),
        ], 201);
    }

    public function show(Rol $rol)
    {
        return response()->json([
            'data' => $this->payload($rol->load('permisos')),
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('rols', 'nombre')->ignore($rol->id)],
            'permiso_ids' => ['array'],
            'permiso_ids.*' => ['integer', 'exists:permisos,id'],
        ]);

        $rol->update([
            'nombre' => $data['nombre'],
        ]);

        $rol->permisos()->sync($data['permiso_ids'] ?? []);

        return response()->json([
            'message' => 'Rol actualizado.',
            'data' => $this->payload($rol->fresh(['permisos'])),
        ]);
    }

    public function destroy(Rol $rol)
    {
        if ($rol->users()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un rol con usuarios asignados.',
            ], 422);
        }

        $rol->permisos()->detach();
        $rol->delete();

        return response()->json([
            'message' => 'Rol eliminado.',
        ]);
    }

    public function permissions(Request $request, Rol $rol)
    {
        $data = $request->validate([
            'permiso_ids' => ['required', 'array'],
            'permiso_ids.*' => ['integer', 'exists:permisos,id'],
        ]);

        $rol->permisos()->sync($data['permiso_ids']);

        return response()->json([
            'message' => 'Permisos actualizados.',
            'data' => $this->payload($rol->load('permisos')),
        ]);
    }

    private function payload(Rol $rol): array
    {
        return [
            'id' => $rol->id,
            'nombre' => $rol->nombre,
            'users_count' => $rol->users_count ?? $rol->users()->count(),
            'permisos' => $rol->permisos->map(fn ($permiso) => [
                'id' => $permiso->id,
                'nombre' => $permiso->nombre,
            ])->values(),
        ];
    }

        public function updatepermisos(Request $request){
        $permisos= array();
        $rol=Rol::find($request->id);
        foreach ($request->permisos as $permiso){
            if ($permiso['estado']==true)
                $permisos[]=$permiso['id'];
            foreach ($permiso['sub_permisos'] as $subpermiso){
                if ($subpermiso['estado']==true) {
                    $permisos[]=$subpermiso['id'];
                }
            }
        }

        $permiso = Permiso::find($permisos);
        $rol->permisos()->detach();
        $rol->permisos()->attach($permiso);
    }
}
