<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\HardwareController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\WebappController;
use App\Http\Controllers\Webserver;
use App\Http\Controllers\WebserverController;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login',   [AuthController::class, 'login']    )->name('login');
    Route::post('/logout',  [AuthController::class, 'logout']   )->name('logout')->middleware('auth:sanctum');
});


Route::get('node/all',                      [NodeController::class, 'all']      )->name('node.all');
Route::get('node/uptime',                   [NodeController::class, 'uptime']   )->name('node.uptime');
Route::get('node/os',                       [NodeController::class, 'os']       )->name('node.os');
Route::get('node/ip',                       [NodeController::class, 'ip']       )->name('node.ip');

Route::get('node/hardware/cpu',             [HardwareController::class, 'cpu']      )->name('node.hardware.cpu');
Route::get('node/hardware/memory',          [HardwareController::class, 'memory']   )->name('node.hardware.memory');
Route::get('node/hardware/disk',            [HardwareController::class, 'disk']     )->name('node.hardware.disk');
Route::get('node/hardware/network',         [HardwareController::class, 'network']  )->name('node.hardware.network');

Route::get('usage/',                        [UsageController::class, 'get_usage']    )->name('usage');
Route::get('usage/cpu',                     [UsageController::class, 'cpu_usage']    )->name('usage.cpu');
Route::get('usage/core/{core}',             [UsageController::class, 'core_usage']   )->name('usage.core');
Route::get('usage/ram',                     [UsageController::class, 'ram_usage']    )->name('usage.ram');
Route::get('usage/disk',                    [UsageController::class, 'disk_usage']   )->name('usage.disk');
Route::get('usage/network',                 [UsageController::class, 'network_usage'])->name('usage.network');

Route::get( 'users/',                       [UserController::class, 'index']    )->name('users.index');
Route::get( 'users/{username}',             [UserController::class, 'show']     )->name('users.show');
Route::post('users/store',                  [UserController::class, 'store']    )->name('users.store');
Route::post('users/{username}/update',      [UserController::class, 'update']   )->name('users.update');
Route::post('users/{username}/destroy',     [UserController::class, 'destroy']  )->name('users.destroy');

Route::get( 'network/',                     [NetworkController::class, 'index']     )->name('network.index');
Route::post('network/store',                [NetworkController::class, 'store']     )->name('network.store');
Route::get( 'network/{port}',               [NetworkController::class, 'show']      )->name('network.show')->where('port', '[0-9]+');
Route::get( 'network/locked',               [NetworkController::class, 'showLocked'])->name('network.blocked');
Route::post('network/{port}/destory',       [NetworkController::class, 'destroy']   )->name('network.destroy')->where('port', '[0-9]+');

Route::get( 'webserver',                        [WebserverController::class, 'index']   )->name('webserver.index');
Route::post('webserver/store',                  [WebserverController::class, 'store']   )->name('webserver.store');
Route::get( 'webserver/{file_name}',            [WebserverController::class, 'show']    )->name('webserver.show')->where('file_name', '^(?!store$)[a-zA-Z0-9-_.]+$');
Route::post('webserver/{file_name}/update',     [WebserverController::class, 'update']  )->name('webserver.update');
Route::post('webserver/{file_name}/destroy',    [WebserverController::class, 'destroy'] )->name('webserver.destroy');
Route::post('webserver/{file_name}/certbot',    [WebserverController::class, 'certbot'] )->name('webserver.certbot');
Route::post('webserver/{file_name}/enable',     [WebserverController::class, 'enable']  )->name('webserver.enable');
Route::post('webserver/{file_name}/disable',    [WebserverController::class, 'disable'] )->name('webserver.disable');

Route::get( 'webapp/',                      [WebappController::class, 'index']      )->name('webapp.index');
Route::post('webapp/store',                 [WebappController::class, 'store']      )->name('webapp.store');
Route::get( 'webapp/{project_name}',        [WebappController::class, 'show']       )->name('webapp.show')->where('project_name', '^(?!store$)[a-zA-Z0-9-_.]+$');
Route::post('webapp/{project_name}/update', [WebappController::class, 'update']     )->name('webapp.update');
Route::post('webapp/{project_name}/destroy',[WebappController::class, 'destroy']    )->name('webapp.destroy');
