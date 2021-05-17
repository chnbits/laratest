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
        $data['uid'] = $adminId;

        $res = $this->insertData($this->menu_table,$data);
        if (!$res){
            $this->opRec($adminId,'菜单模块',$data,'添加菜单',1);
            return $this->res(1,'添加失败！');
        }
        $this->opRec($adminId,'角色模块',$data,'添加菜单',0);
        return $this->res(0,'添加成功！');
    }
    //修改菜单
    public function editMenu(Request $request)
    {
        $adminId = $request->admin->userId;

        $data = $request->except('isShow','menuId','children');
        $menuId = $request->post('menuId');

        $res = $this->updateData($this->menu_table,'menuId',$menuId,$data);
        if (!$res){
            $this->opRec($adminId,'菜单模块',$data,'修改菜单',1);
            return $this->res(1,'修改失败！');
        }
        $this->opRec($adminId,'菜单模块',$data,'修改菜单',0);
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
            $this->opRec($adminId,'菜单模块',$id,'删除菜单',1);
            return $this->res(1, '删除失败！');
        }
        $this->opRec($adminId,'菜单模块',$id,'删除菜单',0);
        return $this->res(0, '删除成功！');
    }
}
