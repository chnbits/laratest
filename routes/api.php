<?php

use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\System\DictController;
use App\Http\Controllers\Admin\System\LogController;
use App\Http\Controllers\Admin\System\MenuController;
use App\Http\Controllers\Admin\System\OrganizeController;
use App\Http\Controllers\Admin\System\RoleController;
use App\Http\Controllers\Admin\System\UserController;
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
    Route::put('/main/profile/{id}',[MainController::class,'profile']);
    Route::put('/main/password',[MainController::class,'changePsw']);

    Route::post('/file/upload',[FileController::class,'upload']);
    Route::post('/file/uploads',[FileController::class,'uploads']);
    Route::get('/file/list',[FileController::class,'getFileList']);
    Route::post('/file/create',[FileController::class,'createFile']);
    Route::post('/file/delete',[FileController::class,'deleteFile']);
    Route::get('/file/dir',[FileController::class,'getDir']);
    Route::post('/file/move',[FileController::class,'moveFiles']);

    Route::get('/sys/loginRecord/page',[LogController::class,'loginRecord']);
    Route::get('/sys/operRecord/page',[LogController::class,'oplog']);

    Route::get('/sys/user/page',[UserController::class,'getUserPage']);
    Route::get('/sys/role',[UserController::class,'getRole']);
    Route::put('/sys/user/state/{id}',[UserController::class,'changeUserState']);
    Route::put('/sys/user',[UserController::class,'editUser']);
    Route::post('/sys/user',[UserController::class,'createUser']);
    Route::post('sys/user/import',[UserController::class,'importUser']);
    Route::post('sys/user/batch',[UserController::class,'deleteUser']);
    Route::post('/sys/user/{id}',[UserController::class,'deleteUser']);

    Route::get('/sys/role/page',[RoleController::class,'getRolePage']);
    Route::get('/sys/role/menu',[RoleController::class,'getRoleMenu']);
    Route::put('/sys/role',[RoleController::class,'editRole']);
    Route::post('/sys/role',[RoleController::class,'createRole']);
    Route::put('/sys/role/batch',[RoleController::class,'deleteRole']);
    Route::put('/sys/role/{id}',[RoleController::class,'deleteRole']);
    Route::put('/sys/role/menu/{id}',[RoleController::class,'editRoleRight']);

    Route::get('/sys/menu',[MenuController::class,'getMenu']);
    Route::post('/sys/menu',[MenuController::class,'createMenu']);
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

    Route::get('/sys/organization',[OrganizeController::class,'getOrganize']);
    Route::get('/sys/dictdata',[OrganizeController::class,'getDictData']);
    Route::post('/sys/organization',[OrganizeController::class,'createOrganize']);
    Route::put('/sys/organization',[OrganizeController::class,'createOrganize']);
    Route::put('/sys/organization/{id}',[OrganizeController::class,'deleteOrganize']);
});
