<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with(['rol.permisos'])
            ->where('name', $credentials['name'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales invalidas.',
            ], 422);
        }

        if ($user->estado !== 'ACTIVO') {
            return response()->json([
                'message' => 'El usuario esta inactivo.',
            ], 403);
        }

        if ($user->fecha_limite && $user->fecha_limite->lessThanOrEqualTo(Carbon::today())) {
            return response()->json([
                'message' => 'La cuenta esta vencida.',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('expendio')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->profilePayload($user->fresh(['rol.permisos'])),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['rol.permisos']);

        return response()->json([
            'user' => $this->profilePayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesion cerrada.',
        ]);
    }

    private function profilePayload(User $user): array
    {
        return [
            'id' => $user->id,
            'cedula' => $user->cedula,
            'name' => $user->name,
            'nombre' => $user->nombre,
            'celular' => $user->celular,
            'fecha_limite' => optional($user->fecha_limite)->toDateString(),
            'estado' => $user->estado,
            'email' => $user->email,
            'rol' => $user->rol ? [
                'id' => $user->rol->id,
                'nombre' => $user->rol->nombre,
            ] : null,
            'permisos' => $user->rol ? $user->rol->permisos->map(fn ($permiso) => [
                'id' => $permiso->id,
                'nombre' => $permiso->nombre,
            ])->values() : [],
        ];
    }
}
