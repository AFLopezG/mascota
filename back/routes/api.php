<?php

use App\Http\Controllers\PermisoController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\RolController;
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

    Route::resource('/user',\App\Http\Controllers\UserController::class);
    Route::resource('/rol',\App\Http\Controllers\RolController::class);
    Route::resource('/permiso',\App\Http\Controllers\PermisoController::class);
    Route::resource('/persona', PersonaController::class);
    Route::resource('/mascota', MascotaController::class);
    Route::resource('/vacuna', VacunaController::class);


});
