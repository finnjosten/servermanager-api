<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\AuthController;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */


Route::get('/', function () { abort(404); });

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login',   [AuthController::class, 'login']    )->name('login');
    Route::post('/logout',  [AuthController::class, 'logout']   )->name('logout')->middleware('auth:sanctum');
});


Route::get      ('node/uptime',                 [NodeController::class, 'uptime']   )->name('node.uptime');
Route::get      ('node/os',                     [NodeController::class, 'os']       )->name('node.os');
Route::get      ('node/ip',                     [NodeController::class, 'ip']       )->name('node.ip');


Route::get      ('users/',                      [UserController::class, 'index']    )->name('users.index');
Route::get      ('users/{username}',            [UserController::class, 'show']     )->name('users.show');
Route::get      ('users/store',                 [UserController::class, 'store']    )->name('users.store');
Route::get      ('users/update/{username}',     [UserController::class, 'update']   )->name('users.update');
Route::get      ('users/destroy/{username}',    [UserController::class, 'destroy']  )->name('users.destroy');
