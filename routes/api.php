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
use App\Http\Controllers\UnitController;
use App\Http\Controllers\DftHargaJualController;
use App\Http\Controllers\DftHargaBeliController;

use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\PurchaseOrdersController;
use App\Http\Controllers\PembeliansController;

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
    Route::resource("unit", UnitController::class);
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

    Route::resource("purchase-orders", PurchaseOrdersController::class);
    Route::get("purchase-orders/{from}/{to}",[PurchaseOrdersController::class,'index']);
    Route::get('purchase-orders-supplier',[PurchaseOrdersController::class,'supplier']);
    Route::get('purchase-orders-barang',[PurchaseOrdersController::class,'barang']);
    Route::get('purchase-orders-gudang',[PurchaseOrdersController::class,'gudang']);
    Route::get('purchase-orders-uang',[PurchaseOrdersController::class,'uang']);
    Route::put('batal-po/{id}',[PurchaseOrdersController::class,'batalin']);
    Route::get('purchase-orders-wo',[PurchaseOrdersController::class,'wo']);

    Route::get('purchase-orders-jasa/{from}/{to}',[PurchaseOrdersController::class,'indexJasa']);
    Route::post('purchase-orders-jasa',[PurchaseOrdersController::class,'storeJasa']);
    Route::get('purchase-orders-jasa/{id}',[PurchaseOrdersController::class,'showJasa']);
    Route::put('purchase-orders-jasa/{id}',[PurchaseOrdersController::class,'updateJasa']);

    Route::get('wip-processing/{from}/{to}',[WorkOrderController::class,'wipProcessing']);
    Route::put('wip-processing/{id}',[WorkOrderController::class,'updateWipProc']);
    Route::get('wip-finance/{from}/{to}',[WorkOrderController::class,'wipFinance']);
    Route::put('wip-finance/{id}',[WorkOrderController::class,'updateWipFinanc']);
    Route::get('wip-claim/{from}/{to}',[WorkOrderController::class,'wipClaim']);
    Route::put('wip-claim/{id}',[WorkOrderController::class,'updateWipClaim']);
    Route::get('wip-scm/{from}/{to}',[WorkOrderController::class,'wipScm']);
    Route::put('wip-scm/{id}',[WorkOrderController::class,'updateWipScm']);
    Route::get('wip-analis/{from}/{to}',[WorkOrderController::class,'wipAnalis']);
    Route::put('wip-analis/{id}',[WorkOrderController::class,'updateWipAnalis']);
    Route::get('wo-lessor',[WorkOrderController::class,'lessor']);
    Route::put('wo-ownrisk/{id}',[WorkOrderController::class,'updateOwnRisk']);
    Route::get('wo-pic-scm',[WorkOrderController::class,'picScm']);

    Route::resource("workOrder",WorkOrderController::class);
    Route::put("contextMenu-wo/{id}",[WorkOrderController::class,'contextMenu']);
    Route::get("workOrder/{from}/{to}",[WorkOrderController::class,'index']);
    Route::put('report-wo/{id}',[WorkOrderController::class,'report']);
    Route::put("batal-workOrder/{id}",[WorkOrderController::class, 'batalin']);

    Route::resource("pembelian", PembeliansController::class);
    Route::get("pembelian/{from}/{to}",[PembeliansController::class,'index']);
    Route::get('pembelian-uang',[PembeliansController::class,'uang']);
    Route::get('pembelian-supplier',[PembeliansController::class,'supplier']);
    Route::get('pembelian-barang',[PembeliansController::class,'barang']);
    Route::get('pembelian-gudang',[PembeliansController::class,'gudang']);
    Route::get("pembelian-po",[PembeliansController::class, 'dataPo']);
    Route::get("items-po/{id}",[PembeliansController::class,'itemsPo']);
    Route::put("batal-beli/{id}",[PembeliansController::class,'batalin']);
    Route::put('grid-beli/{id}',[PembeliansController::class,'updateGrid']);
    Route::get('pembelian-jasa/{from}/{to}',[PembeliansController::class,'indexJasa']);
    Route::post('pembelian-jasa',[PembeliansController::class,'storeJasa']);
    Route::get('pembelian-jasa/{id}',[PembeliansController::class,'showJasa']);
    Route::put('pembelian-jasa/{id}',[PembeliansController::class,'updateJasa']);
    Route::get("pembelian-po-jasa",[PembeliansController::class, 'dataPoJasa']);
    Route::get("items-po-jasa/{id}",[PembeliansController::class,'itemsPoJasa']);
    Route::get("beli/cekPelunasan/{id}",[PembeliansController::class,'cekPelunasan']);
    Route::get("report-pembelian/{id}",[PembeliansController::class,'report']);
});
