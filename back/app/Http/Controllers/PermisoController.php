<?php

namespace App\Http\Controllers;

use App\Models\Permiso;

class PermisoController extends Controller
{
    public function index()
    {
        return Permiso::whereNull('permiso_id')->with('subPermisos')->orderBy('id', 'asc')->get();

    }
}
