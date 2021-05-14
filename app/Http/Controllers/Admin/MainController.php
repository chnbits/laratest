<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainController extends BaseController
{
    public function getAdmin(Request $request)
    {
        $user = $request->admin;
        if (!$user){
            return $this->res(1,'还未登录！');
        }
        $userInfo = json_decode($user,true);
        $roleIds_arr = json_decode($userInfo['roleIds']);
        $roles = DB::table($this->role_table)->whereIn('roleId',$roleIds_arr)->get()->all();
        $temp = array();
        foreach ($roles as $value)
        {
            $temp = array_merge($temp,json_decode($value->roleMenu));
        }
        $menuIds_arr = array_unique($temp);
        $menus = DB::table($this->menu_table)->whereIn('menuId',$menuIds_arr)->get()->all();
        $userInfo['roles'] = $roles;
        $userInfo['authorities'] = $menus;
        $sex = $user->sex;

        $dictId = $this->getData($this->dict_table,'dictCode','sex','dictId')->first();
        $userInfo['sexName'] = DB::table($this->dictData_table)->where('dictId',$dictId->dictId)->where('dictDataValue',$sex)->get('dictDataName')->first();
        unset($userInfo['password']);

        return $this->res(0,'SUCCESS!','',$userInfo);
    }
    public function menus(Request $request)
    {
        $user = $request->admin;
        $roleIds = $user->roleIds;
        $roleIds_arr = json_decode($roleIds);
        $roles = DB::table($this->role_table)->whereIn('roleId',$roleIds_arr)->get()->all();

        $temp = array();
        foreach ($roles as $value)
        {
            $temp = array_merge($temp,json_decode($value->roleMenu));
        }
        $menuIds_arr = array_unique($temp);

        $res = DB::table($this->menu_table)->where('hide',0)->where('menuType',0)->whereIn('menuId',$menuIds_arr)->get()->all();

        if (!$res){
            return $this->res(1,'没有找到菜单！');
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
        return $this->res(0,'SUCCESS!','',$data);
    }
    public function profile(Request $request)
    {

    }
}
