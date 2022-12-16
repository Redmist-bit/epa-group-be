<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BahasaSistemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeriodesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CoaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::get("bahasa/{bahasa}",[BahasaSistemController::class, 'index']);
Route::post("login", [AuthController::class, 'login']);
Route::post("register", [AuthController::class, 'register']);
Route::group([
    "middleware" => "auth.jwt"
], function(){
    Route::resource("periodes", PeriodesController::class);
    Route::get("period",[PeriodesController::class,'period']);
    Route::post("logout",[AuthController::class, 'logout']);
    Route::get("userdata",[AuthController::class, 'userdata']);
    Route::post("reset", [AuthController::class, 'ResetPassword']);
    Route::resource("user", UserController::class);
    Route::put("resetPwd/{id}",[UserController::class,'resetPwd']);
    Route::resource("coa", CoaController::class);
});
