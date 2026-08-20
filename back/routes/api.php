<?php

use App\Http\Controllers\CampaniaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\HealthCenterController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RazaController;
use App\Http\Controllers\CampaniaTipoController;
use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\DenunciaTipoController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\RegistroVacunaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacunaController;
use App\Http\Controllers\PersonalController;

use Illuminate\Support\Facades\Route;

Route::post('/login',[\App\Http\Controllers\UserController::class,'login'])->name('login');
Route::get('/public/mascota/{codigo}', [MascotaController::class, 'publicShow']);

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
    Route::get('/denuncia/reporte', [DenunciaController::class, 'reporte']);
    Route::post('/denuncia/{denuncia}/logs', [DenunciaController::class, 'storeLog']);
    Route::post('/campania', [CampaniaController::class, 'store']);
    Route::put('/campania/{campania}', [CampaniaController::class, 'update']);
    Route::put('/campania/{campania}/anular', [CampaniaController::class, 'anular']);
    Route::delete('/campania/{campania}', [CampaniaController::class, 'destroy']);
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
    Route::resource('/personal', PersonalController::class);
    Route::resource('/place', PlaceController::class);
    Route::resource('/health-center', HealthCenterController::class);
    Route::get('/registro-vacuna/reporte', [RegistroVacunaController::class, 'reporte']);
    Route::get('/registro-vacuna/reporte/pdf', [RegistroVacunaController::class, 'reportePdf']);
    Route::get('/registro-vacuna/{registroVacuna}/foto', [RegistroVacunaController::class, 'foto'])->whereNumber('registroVacuna');
    Route::resource('/registro-vacuna', RegistroVacunaController::class);
    Route::put('/registro-vacuna/{registroVacuna}/anular', [RegistroVacunaController::class, 'anular']);
    Route::post('/mascota/{mascota}/foto', [MascotaController::class, 'updateFoto']);
    Route::post('/mascota/{mascota}/fallecimiento', [MascotaController::class, 'updateFallecimiento']);
    Route::resource('/proceso', ProcesoController::class);
    Route::resource('/mascota', MascotaController::class);
    Route::resource('/vacuna', VacunaController::class);
    Route::resource('/denuncia', DenunciaController::class);
    Route::resource('/denuncia-tipo', DenunciaTipoController::class);

    Route::get('/public/mascota/{codigo}/pdf', [MascotaController::class, 'publicCredentialPdf']);

});
