<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MainController extends BaseController
{
    public function getAdmin(Request $request)
    {
        $user = $request->admin;
        if (!$user){
            return response()->json([
                'code'=>1,
                'msg'=>'还未登录！'
            ]);
        }
        $userInfo = json_decode($user,true);
        $roleIds_arr = json_decode($userInfo['roleIds']);
        $roles = DB::table('roles')->whereIn('roleId',$roleIds_arr)->get()->all();
        $temp = array();
        foreach ($roles as $value)
        {
            $temp = array_merge($temp,json_decode($value->roleMenu));
        }
        $menuIds_arr = array_unique($temp);
        $menus = DB::table('menus')->whereIn('menuId',$menuIds_arr)->get()->all();
        $userInfo['roles'] = $roles;
        $userInfo['authorities'] = $menus;
        $data = array(
            'code'=>0,
            'msg'=>'Success',
            'data'=>$userInfo,
        );

        return response()->json($data);
    }
    public function menus(Request $request)
    {
        $user = $request->admin;
        $roleIds = $user->roleIds;
        $roleIds_arr = json_decode($roleIds);
        $roles = DB::table('roles')->whereIn('roleId',$roleIds_arr)->get()->all();

        $temp = array();
        foreach ($roles as $value)
        {
            $temp = array_merge($temp,json_decode($value->roleMenu));
        }
        $menuIds_arr = array_unique($temp);

        $res = DB::table('menus')->where('isShow',0)->where('menuType',0)->whereIn('menuId',$menuIds_arr)->get()->all();

        if (!$res){
            return response()->json([
                'code'=>1,
                'msg'=>'没有找到菜单！'
            ]);
        }
        $temp = array();
        foreach($res as $value){
            $temp[$value->menuId] = $value;
        }
        $data = array();
        foreach($temp as $value){
            if ($value->parentId != 0 ){
                $temp[$value->parentId]->children[] = $temp[$value->menuId];
            }else{
                $data[] = $temp[$value->menuId];
            }
        }
        return response()->json([
            'code'=>0,
            'msg'=>'SUCCESS!',
            'data'=>$data
        ]);
    }
}