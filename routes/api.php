<?php

use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\SystemController;
use Illuminate\Http\Request;
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

    Route::get('/sys/loginRecord/page',[SystemController::class,'loginRecord']);

    Route::get('/sys/user/page',[SystemController::class,'getUserPage']);
    Route::get('/sys/role',[SystemController::class,'getRole']);
    Route::put('/sys/user/state/{id}',[SystemController::class,'changeUserState']);
    Route::put('/sys/user',[SystemController::class,'editUser']);
    Route::post('/sys/user',[SystemController::class,'createUser']);
    Route::post('sys/user/import',[SystemController::class,'importUser']);
    Route::post('/sys/user/{id}',[SystemController::class,'deleteUser']);
    Route::post('sys/user/batch',[SystemController::class,'deleteUser']);


    Route::get('/sys/role/page',[SystemController::class,'getRolePage']);
    Route::get('/sys/role/menu',[SystemController::class,'getRoleMenu']);
    Route::put('/sys/role',[SystemController::class,'editRole']);
    Route::post('/sys/role',[SystemController::class,'createRole']);
    Route::put('/sys/role/{id}',[SystemController::class,'deleteRole']);
    Route::put('/sys/role/batch',[SystemController::class,'deleteRole']);
    Route::put('/sys/role/menu/{id}',[SystemController::class,'editRoleRight']);

    Route::get('/sys/menu',[SystemController::class,'getMenu']);
    Route::put('/sys/menu',[SystemController::class,'editMenu']);
    Route::put('/sys/menu/{id}',[SystemController::class,'deleteMenu']);

    Route::get('/sys/dict',[SystemController::class,'getDict']);
    Route::post('/sys/dict',[SystemController::class,'createDict']);
    Route::put('/sys/dict',[SystemController::class,'createDict']);
    Route::put('/sys/dict/{id}',[SystemController::class,'deleteDict']);
    Route::get('/sys/dictdata/page',[SystemController::class,'getDictPage']);
    Route::post('/sys/dictdata',[SystemController::class,'createDictData']);
    Route::put('/sys/dictdata',[SystemController::class,'createDictData']);
    Route::put('/sys/dictdata/{id}',[SystemController::class,'deleteDictData']);
});
