<?php

use App\Http\Controllers\CampaniaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RazaController;
use App\Http\Controllers\CampaniaTipoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacunaController;
use Illuminate\Support\Facades\Route;

Route::post('/login',[\App\Http\Controllers\UserController::class,'login'])->name('login');

//Route::middleware(['auth:sanctum', EnsureAccountIsValid::class])->group(function () {
Route::group(['middleware'=>'auth:sanctum'],function (){
    Route::post('/logout',[\App\Http\Controllers\UserController::class,'logout']);
    Route::post('/me',[\App\Http\Controllers\UserController::class,'me']);
    Route::post('/updatePassword',[\App\Http\Controllers\UserController::class,'updatePassword']);
    Route::get('/listausers',[\App\Http\Controllers\UserController::class,'listausers']);
    Route::post('/updatepermisos',[\App\Http\Controllers\RolController::class,'updatepermisos']);
    Route::post('/cambioEstado',[\App\Http\Controllers\UserController::class,'cambioEstado']);
    Route::get('/buscar-documento', [PersonaController::class, 'buscarDocumento']);
    Route::get('/especie', [EspecieController::class, 'index']);
    Route::get('/categoria', [CategoriaController::class, 'index']);
    Route::get('/raza', [RazaController::class, 'index']);
    Route::get('/campania-tipo', [CampaniaTipoController::class, 'index']);
    Route::get('/campania', [CampaniaController::class, 'index']);
    Route::post('/categoria', [CategoriaController::class, 'store']);
    Route::put('/categoria/{categoria}', [CategoriaController::class, 'update']);
    Route::delete('/categoria/{categoria}', [CategoriaController::class, 'destroy']);
    Route::post('/especie', [EspecieController::class, 'store']);
    Route::put('/especie/{especie}', [EspecieController::class, 'update']);
    Route::delete('/especie/{especie}', [EspecieController::class, 'destroy']);
    Route::post('/raza', [RazaController::class, 'store']);
    Route::put('/raza/{raza}', [RazaController::class, 'update']);
    Route::delete('/raza/{raza}', [RazaController::class, 'destroy']);
    Route::post('/campania-tipo', [CampaniaTipoController::class, 'store']);
    Route::put('/campania-tipo/{campaniaTipo}', [CampaniaTipoController::class, 'update']);
    Route::delete('/campania-tipo/{campaniaTipo}', [CampaniaTipoController::class, 'destroy']);

    Route::resource('/user',\App\Http\Controllers\UserController::class);
    Route::resource('/rol',\App\Http\Controllers\RolController::class);
    Route::resource('/permiso',\App\Http\Controllers\PermisoController::class);
    Route::resource('/persona', PersonaController::class);
    Route::resource('/mascota', MascotaController::class);
    Route::resource('/vacuna', VacunaController::class);


});
