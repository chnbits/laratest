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
        $data = $request->all();
        $data['createTime'] = date('Y-m-d H:i:s',time());
        $res = DB::table($this->role_table)->insert($data);
        if (!$res){
            $this->opRec($adminId,'角色模块','添加角色',1);
            return $this->res(1,'添加失败！');
        }
        $this->opRec($adminId,'角色模块','添加角色',0);
        return $this->res(0,'添加成功！',1);
    }
    //编辑角色
    public function editRole(Request $request)
    {
        $adminId = $request->admin->userId;
        $roleId = $request->roleId;
        $data = $request->except('roleId');
        $res = DB::table($this->role_table)->where('roleId',$roleId)->update($data);
        if (!$res){
            $this->opRec($adminId,'角色模块','编辑角色',1);
            return $this->res(1, '更改失败！');
        }
        $this->opRec($adminId,'角色模块','编辑角色',0);
        return $this->res(0,'更改成功！');
    }
    //获取角色对应菜单
    public function getRoleMenu(Request $request)
    {
        $roleId = $request->roleId;
        $role = DB::table($this->role_table)->where('roleId',$roleId)->get('roleMenu')->first();
        $roleMenu_arr = json_decode($role->roleMenu,true);
        $res = DB::table($this->menu_table)->get()->all();

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
        $updateTime = date('Y-m-d H:i:s');
        $data = array(
            'roleMenu'=>json_encode($menuIds),
            'updateTime'=>$updateTime
        );
        $res = DB::table($this->role_table)->where('roleId',$roleId)->update($data);
        if (!$res){
            $this->opRec($adminId,'角色模块','编辑角色权限',1);
            return $this->res(1,'修改失败！');
        }
        $this->opRec($adminId,'角色模块','编辑角色权限',0);
        return $this->res(0, '修改成功！');
    }
    //删除角色
    public function deleteRole(Request $request,$roleId)
    {
        $adminId = $request->admin->userId;
        $roleIds = $request->data;

        if ($roleId=='batch'){
            $res = DB::table($this->role_table)->whereIn('roleId',$roleIds)->update(['deleted'=>1]);
        }else{
            $res = DB::table($this->role_table)->where('roleId',$roleId)->update(['deleted'=>1]);
        }
        if (!$res){
            $this->opRec($adminId,'角色模块','删除角色',1);
            return $this->res(1, '删除失败！');
        }
        $this->opRec($adminId,'角色模块','删除角色',0);
        return $this->res(0,'删除成功！');
    }
}
