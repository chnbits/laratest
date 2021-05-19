<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends BaseController
{
    //角色列表
    public function getRolePage(Request $request)
    {
        $limit = $request->get('limit');
        $roleName = $request->get('roleName');
        $roleCode = $request->get('roleCode');
        $comments = $request->get('comments');
        $res = DB::table($this->role_table)
            ->where('deleted',0)
            ->where('roleCode','like','%'.$roleCode.'%')
            ->where('roleName','like','%'.$roleName.'%')
            ->where('comments','like','%'.$comments.'%')
            ->paginate($limit);
        $roles = $res->items();
        $count = $res->total();
        if ($count == 0){
            return $this->res(1,'没有数据！');
        }
        return $this->res(0,'SUCCESS',$count,$roles);
    }
    //添加角色
    public function createRole(Request $request)
    {
        $adminId = $request->admin->userId;
        $data = $request->only('roleName','roleCode','comments');

        $res = $this->insertData($this->role_table,$data);

        if (!$res){
            $this->opRec($adminId,'角色模块',$data,'添加角色',1);
            return $this->res(1,'添加失败！');
        }
        $this->opRec($adminId,'角色模块',$data,'添加角色',0);
        return $this->res(0,'添加成功！',1);
    }
    //编辑角色
    public function editRole(Request $request)
    {
        $adminId = $request->admin->userId;
        $roleId = $request->roleId;
        $data = $request->only('roleName','roleCode','comments');

        $res = $this->updateData($this->role_table,'roleId',$roleId,$data);

        if (!$res){
            $this->opRec($adminId,'角色模块',$data,'编辑角色',1);
            return $this->res(1, '更改失败！');
        }
        $this->opRec($adminId,'角色模块',$data,'编辑角色',0);
        return $this->res(0,'更改成功！');
    }
    //获取角色对应菜单
    public function getRoleMenu(Request $request)
    {
        $roleId = $request->roleId;
        $role = $this->getData($this->role_table,'roleId',$roleId,'roleMenu')->first();
        $roleMenu_arr = json_decode($role->roleMenu,true);

        $res = $this->getData($this->menu_table,'deleted',0,['title','menuId','parentId'])->all();

        foreach ($res as $value)
        {
            $value->checked = in_array($value->menuId,$roleMenu_arr)? true: false;
        }
        if (!$res){
            return $this->res(1,'菜单获取失败！');
        }
        return $this->res(0,'SUCCESS', '',$res);
    }
    //编辑权限
    public function editRoleRight(Request $request,$roleId)
    {
        $adminId = $request->admin->userId;
        $menuIds = $request->all();
        $parm['roleId'] = $roleId;
        $parm['roleMenu'] = $data['roleMenu'] = json_encode($menuIds);

        $res = $this->updateData($this->role_table,'roleId',$roleId,$data);
        if (!$res){
            $this->opRec($adminId,'角色模块',$parm,'编辑角色权限',1);
            return $this->res(1,'修改失败！');
        }
        $this->opRec($adminId,'角色模块',$parm,'编辑角色权限',0);
        return $this->res(0, '修改成功！');
    }
    //删除角色
    public function deleteRole(Request $request,$roleId='')
    {
        $adminId = $request->admin->userId;
        $roleIds = $request->data;

        if (!empty($roleIds)){
            $res = $this->deletePatch($this->role_table,'roleId',$roleIds);
            $data = $roleIds;
        }else{
            $res = $this->deleteData($this->role_table,'roleId',$roleId);
            $data = $roleId;
        }
        if (!$res){
            $this->opRec($adminId,'角色模块',$data,'删除角色',1);
            return $this->res(1, '删除失败！');
        }
        $this->opRec($adminId,'角色模块',$data,'删除角色',0);
        return $this->res(0,'删除成功！');
    }
}
