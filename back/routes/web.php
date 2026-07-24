<?php

use App\Http\Controllers\CampaniaTipoController;
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\RazaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('catalogos')->group(function () {
    Route::get('/especies', [EspecieController::class, 'manage'])->name('catalogos.especies');
    Route::post('/especies', [EspecieController::class, 'store'])->name('catalogos.especies.store');
    Route::get('/especies/{especie}/editar', [EspecieController::class, 'edit'])->name('catalogos.especies.edit');
    Route::put('/especies/{especie}', [EspecieController::class, 'update'])->name('catalogos.especies.update');

    Route::get('/razas', [RazaController::class, 'manage'])->name('catalogos.razas');
    Route::post('/razas', [RazaController::class, 'store'])->name('catalogos.razas.store');
    Route::get('/razas/{raza}/editar', [RazaController::class, 'edit'])->name('catalogos.razas.edit');
    Route::put('/razas/{raza}', [RazaController::class, 'update'])->name('catalogos.razas.update');

    Route::get('/campania-tipos', [CampaniaTipoController::class, 'manage'])->name('catalogos.campania-tipos');
    Route::post('/campania-tipos', [CampaniaTipoController::class, 'store'])->name('catalogos.campania-tipos.store');
    Route::get('/campania-tipos/{campaniaTipo}/editar', [CampaniaTipoController::class, 'edit'])->name('catalogos.campania-tipos.edit');
    Route::put('/campania-tipos/{campaniaTipo}', [CampaniaTipoController::class, 'update'])->name('catalogos.campania-tipos.update');
});
