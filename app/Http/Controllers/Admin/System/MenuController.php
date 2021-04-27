<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends BaseController
{
    //获取菜单列表
    public function getMenu(Request $request)
    {
        $title = $request->get('title');
        $path = $request->get('path');
        if (!$path){
            $res = DB::table($this->menu_table)
                ->where('deleted',0)
                ->where('title','like','%'.$title.'%')
                ->orderBy('sortNumber','ASC')
                ->get()->all();
        }else{
            $res = DB::table($this->menu_table)
                ->where('deleted',0)
                ->where('title','like','%'.$title.'%')
                ->where('path','like','%'.$path.'%')
                ->orderBy('sortNumber','ASC')
                ->get()->all();
        }

        if (!$res){
            return $this->res(1,'没有找到菜单！');
        }
        return $this->res(0,'SUCCESS','', $res);
    }
    //添加菜单
    public function createMenu(Request $request)
    {
        $adminId = $request->admin->userId;
        $data = $request->all();
        $data['isShow'] = $request->post('isShow')?0:1;
        $data['createTime'] = date('Y-m-d H:i:s',time());
        $res = DB::table($this->menu_table)->insert($data);
        if (!$res){
            $this->opRec($adminId,'菜单模块','添加菜单',1);
            return $this->res(1,'添加失败！');
        }
        $this->opRec($adminId,'角色模块','添加菜单',0);
        return $this->res(0,'添加成功！');
    }
    //修改菜单
    public function editMenu(Request $request)
    {
        $adminId = $request->admin->userId;

        $data = $request->except('menuId','children');
        $data['isShow'] = $request->post('isShow')?0:1;
        $data['updateTime'] = date('Y-m-d H:i:s',time());
        $menuId = $request->post('menuId');
        $res = DB::table($this->menu_table)->where('menuId',$menuId)->update($data);
        if (!$res){
            $this->opRec($adminId,'菜单模块','修改菜单',1);
            return $this->res(1,'修改失败！');
        }
        $this->opRec($adminId,'菜单模块','修改菜单',0);
        return $this->res(0,'修改成功！');
    }
    //删除菜单
    public function deleteMenu(Request $request,$id)
    {
        $adminId = $request->admin->userId;

        $table = $this->menu_table;
        $column = 'menuId';
        $res = $this->deleteData($table,$column,$id);

        if (!$res) {
            $this->opRec($adminId,'菜单模块','删除菜单',1);
            return $this->res(1, '删除失败！');
        }
        $this->opRec($adminId,'菜单模块','删除菜单',0);
        return $this->res(0, '删除成功！');
    }
}