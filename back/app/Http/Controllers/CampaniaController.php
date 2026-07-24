<?php

namespace App\Http\Controllers;

use App\Models\Campania;

class CampaniaController extends Controller
{
    public function index()
    {
        return Campania::orderByDesc('fec_ini')->get();
    }
}
