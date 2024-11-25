<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\HardwareController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsageController;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */


Route::get('/', function () { abort(404); });

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login',   [AuthController::class, 'login']    )->name('login');
    Route::post('/logout',  [AuthController::class, 'logout']   )->name('logout')->middleware('auth:sanctum');
});


Route::get('node/all',                      [NodeController::class, 'all']   )->name('node.uptime');

Route::get('node/uptime',                   [NodeController::class, 'uptime']   )->name('node.uptime');
Route::get('node/os',                       [NodeController::class, 'os']       )->name('node.os');
Route::get('node/ip',                       [NodeController::class, 'ip']       )->name('node.ip');

Route::get('node/hardware/cpu',             [HardwareController::class, 'cpu']      )->name('node.hardware.cpu');
Route::get('node/hardware/memory',          [HardwareController::class, 'memory']   )->name('node.hardware.memory');
Route::get('node/hardware/disk',            [HardwareController::class, 'disk']     )->name('node.hardware.disk');
Route::get('node/hardware/network',         [HardwareController::class, 'network']  )->name('node.hardware.network');

Route::get('node/usage/',                   [UsageController::class, 'get_usage']    )->name('node.usage');
Route::get('node/usage/cpu',                [UsageController::class, 'cpu_usage']    )->name('node.usage.cpu');
Route::get('node/usage/core/{core}',        [UsageController::class, 'core_usage']   )->name('node.usage.core');
Route::get('node/usage/ram',                [UsageController::class, 'ram_usage']    )->name('node.usage.ram');
Route::get('node/usage/disk',               [UsageController::class, 'disk_usage']   )->name('node.usage.disk');
Route::get('node/usage/network',            [UsageController::class, 'network_usage'])->name('node.usage.network');

Route::get('users/',                        [UserController::class, 'index']    )->name('users.index');
Route::get('users/{username}',              [UserController::class, 'show']     )->name('users.show');
Route::get('users/store',                   [UserController::class, 'store']    )->name('users.store');
Route::get('users/update/{username}',       [UserController::class, 'update']   )->name('users.update');
Route::get('users/destroy/{username}',      [UserController::class, 'destroy']  )->name('users.destroy');
