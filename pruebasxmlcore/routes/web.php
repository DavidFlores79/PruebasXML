<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();


Route::middleware(['auth'])->group(function () {
    
    //usuarios
    Route::get('home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    
    Route::get('usuarios', [App\Http\Controllers\UserController::class, 'index']);
    Route::get('get-usuarios', [App\Http\Controllers\UserController::class, 'getUsuarios']);
    Route::post('usuarios/create', [App\Http\Controllers\UserController::class, 'create']);
    Route::post('usuarios/edit', [App\Http\Controllers\UserController::class, 'edit']);
    Route::post('usuarios', [App\Http\Controllers\UserController::class, 'store']);
    Route::put('usuarios', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete("usuarios/{id}", [App\Http\Controllers\UserController::class, "destroy"]);
    
    Route::post('cargaxml', [App\Http\Controllers\XmlCargaController::class, 'cargaXml']);
    Route::post('cargazip', [App\Http\Controllers\XmlCargaController::class, 'cargaZip']);
});

