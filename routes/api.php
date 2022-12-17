<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BahasaSistemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeriodesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\GudangsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\BarangsController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\MataUangsController;
use App\Http\Controllers\DftHargaJualController;
use App\Http\Controllers\DftHargaBeliController;

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
    Route::resource("mataUangs", MataUangsController::class);
    Route::resource("barangs", BarangsController::class);
    Route::get("merk-barang", [BarangsController::class, 'merk']);
    Route::resource("gudangs", GudangsController::class);
    Route::get("period",[PeriodesController::class,'period']);
    Route::post("logout",[AuthController::class, 'logout']);
    Route::get("userdata",[AuthController::class, 'userdata']);
    Route::post("reset", [AuthController::class, 'ResetPassword']);
    Route::resource("user", UserController::class);
    Route::put("resetPwd/{id}",[UserController::class,'resetPwd']);
    Route::resource("coa", CoaController::class);
    Route::resource("jabatan", JabatanController::class);
    Route::post("jabatandata",[JabatanController::class, 'jabatandata']);
    Route::resource("Menus", MenuController::class);
    Route::put("Menu/{id}",[MenuController::class,'updateMenu']);
    Route::get('parent',[MenuController::class,'parent']);
    Route::get("search",[CustomersController::class, 'search']);
    Route::resource("customers", CustomersController::class);
    Route::get("grup-customers",[CustomersController::class,'grup']);
    Route::get("asuransi",[CustomersController::class,'asuransi']);
    Route::resource("suppliers", SuppliersController::class);
    Route::get("grup-suppliers",[SuppliersController::class,'grup']);
    Route::get("chunk-supplier",[SuppliersController::class,'suppliers']);
    Route::resource("BrgHrgBeli", DftHargaBeliController::class);
    Route::resource("BrgHrgJual", DftHargaJualController::class);
});
