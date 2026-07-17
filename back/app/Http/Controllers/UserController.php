<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return User::with('rol')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:users,name',
            'nombre' => 'required',
            'cedula' => 'required|unique:users',
            'celular' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'fecha_limite' => 'required',
            'rol_id' => 'required',
        ]);

        $validated['password']=Hash::make($validated['password']);
        $validated['nombre']= strtoupper($request->nombre);
        $validated['cedula']= strtoupper($request->cedula);
        $user = User::create($validated);

        return($user);
        
    }
  

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:users,name,'.$request->id,
            'nombre' => 'required',
            'cedula' => 'required|unique:users,cedula,'.$request->id,
            'celular' => 'required',
            'email' => 'required|email|unique:users,email,'.$request->id,
            'fecha_limite' => 'required',
            'rol_id' => 'required',
        ]);
        
        $user = User::find($request->id);
        $user->name = $request->name;
        $user->nombre = strtoupper($request->nombre);
        $user->cedula = strtoupper($request->cedula);
        $user->celular = $request->celular;
        $user->email = $request->email;
        $user->fecha_limite = $request->fecha_limite;
        $user->rol_id = $request->rol_id;
        $user->save();
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'nuevopassword' => 'required|min:6',
        ]);
        $user = User::find($request->user()->id);
        $user->password=Hash::make($request->nuevopassword);
        $user->save();
        return response(['message' => 'Contraseña actualizada'],200);
    }

    public function upPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6',
        ]);
        $user = User::find($request->user()->id);
        $user->password=Hash::make($request->password);
        $user->save();
        return response(['message' => 'Contraseña actualizada'],200);
    }

    public function login(Request $request)
    {
        $user = User::where('name',$request->cuenta)->where('estado','ACTIVO')->whereDate('fecha_limite','>=',date('Y-m-d'))->first();
        error_log($user);
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $user = User::where('name',$request->cuenta)->with('rol')->first();
                $token = $user->createToken('authToken')->plainTextToken;
                return response(['user' => $user, 'token' => $token]);
            } else {
                return response(['message' => 'Contraseña incorrecta'],500);
            }
        } else {
            return response(['message' => 'Usuario no encontrado'],500);
        }
    }

    public function cambioEstado(Request $request)
    {
        $user = User::find($request->id);
        if ($user->estado=='ACTIVO') {
            $user->estado = 'INACTIVO';
        } else {
            $user->estado = 'ACTIVO';
        }
            $user->save();
    }



    public function me(Request $request)
    {
        $user=User::with('rol')->where('id',$request->user()->id)
                    ->where('estado','ACTIVO')
                    ->whereDate('fecha_limite','>=',date('Y-m-d'))
                    ->firstOrFail();
        return $user;
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response(['message' => 'Sesión cerrada'],200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

}
