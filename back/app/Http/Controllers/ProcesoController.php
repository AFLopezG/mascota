<?php

namespace App\Http\Controllers;

use App\Models\Proceso;

class ProcesoController extends Controller
{
    public function index()
    {
        return Proceso::orderBy('orden')->get();
    }
}
