<?php

use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\System\DictController;
use App\Http\Controllers\Admin\System\LogController;
use App\Http\Controllers\Admin\System\MenuController;
use App\Http\Controllers\Admin\System\RoleController;
use App\Http\Controllers\Admin\System\UserController;
use App\Http\Controllers\Admin\SystemController;
use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::get('/file/captcha',[FileController::class,'captcha']);
Route::post('/login',[LoginController::class,'login']);
Route::group(['middleware'=>'auth_passport'],function (){
    Route::get('/main/user',[MainController::class,'getAdmin']);
    Route::get('/main/menu',[MainController::class,'menus']);

    Route::get('/sys/loginRecord/page',[LogController::class,'loginRecord']);
    Route::get('/sys/operRecord/page',[LogController::class,'oplog']);

    Route::get('/sys/user/page',[UserController::class,'getUserPage']);
    Route::get('/sys/role',[UserController::class,'getRole']);
    Route::put('/sys/user/state/{id}',[UserController::class,'changeUserState']);
    Route::put('/sys/user',[UserController::class,'editUser']);
    Route::post('/sys/user',[UserController::class,'createUser']);
    Route::post('sys/user/import',[UserController::class,'importUser']);
    Route::post('/sys/user/{id}',[UserController::class,'deleteUser']);
    Route::post('sys/user/batch',[UserController::class,'deleteUser']);


    Route::get('/sys/role/page',[RoleController::class,'getRolePage']);
    Route::get('/sys/role/menu',[RoleController::class,'getRoleMenu']);
    Route::put('/sys/role',[RoleController::class,'editRole']);
    Route::post('/sys/role',[RoleController::class,'createRole']);
    Route::put('/sys/role/{id}',[RoleController::class,'deleteRole']);
    Route::put('/sys/role/batch',[RoleController::class,'deleteRole']);
    Route::put('/sys/role/menu/{id}',[RoleController::class,'editRoleRight']);

    Route::get('/sys/menu',[MenuController::class,'getMenu']);
    Route::put('/sys/menu',[MenuController::class,'editMenu']);
    Route::put('/sys/menu/{id}',[MenuController::class,'deleteMenu']);

    Route::get('/sys/dict',[DictController::class,'getDict']);
    Route::post('/sys/dict',[DictController::class,'createDict']);
    Route::put('/sys/dict',[DictController::class,'createDict']);
    Route::put('/sys/dict/{id}',[DictController::class,'deleteDict']);
    Route::get('/sys/dictdata/page',[DictController::class,'getDictPage']);
    Route::post('/sys/dictdata',[DictController::class,'createDictData']);
    Route::put('/sys/dictdata',[DictController::class,'createDictData']);
    Route::put('/sys/dictdata/{id}',[DictController::class,'deleteDictData']);
});
