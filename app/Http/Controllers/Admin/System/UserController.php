<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends BaseController
{
    //查询用户
    public function getUserPage(Request $request)
    {
        $limit = $request->get('limit');
        $username = $request->get('username');
        $nickname = $request->get('nickname');
        $sex = $request->get('sex');
        $organizationId = $request->get('organizationId');

        $query = DB::table($this->admin_table)
            ->select('userId','roleIds','username','nickname','phone','state','sex','createTime')
            ->where('deleted',0)
            ->where('username','like','%'.$username.'%')
            ->where('nickname','like','%'.$nickname.'%')
            ->where('sex','like','%'.$sex.'%');
        if (empty($organizationId)) {
            $res = $query->paginate($limit);
        }else{
            $res = $query->where('admins.organizationId',$organizationId)
                ->paginate($limit);
        }
        $users = $res->items();
        $count = $res->total();
        if ($count == 0){
            return $this->res(1,'没有数据！');
        }
        $roles = $this->getData($this->role_table,'deleted',0,['roleId','roleName'])->all();
        $user_data = array();
        foreach ($users as $value)
        {
            $user_data[$value->userId] = $value;
        }
        $role_data = array();
        foreach ($roles as $value)
        {
            $role_data[$value->roleId] = $value;
        }

        $dictId = $this->getData($this->dict_table,'dictCode','sex','dictId')->first();
        $sex_arr = $this->getData($this->dictData_table,'dictId',$dictId->dictId)->all();

        $sex = array();
        foreach ($sex_arr as $val){
            $sex[$val->dictDataValue] = $val;
        }

        foreach ($user_data as $value)
        {
            $roleId_arr = json_decode($value->roleIds,true);
            foreach ($roleId_arr as $v)
            {
                $user_data[$value->userId]->roles[] = $role_data[$v];
            }
            $user_data[$value->userId]->sexName = $sex[$value->sex]->dictDataName;
        }

        return $this->res(0,'SUCCESS',$count,$users);
    }
    //添加用户
    public function createUser(Request $request)
    {
        $adminId = $request->admin->userId;
        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['roleIds'] = json_encode($request->roleIds);
        $data['createTime'] = date('Y-m-d H:i:s',time());

        $res = $this->insertData($this->admin_table,$data);

        if (!$res){
            $this->opRec($adminId,'用户模块','添加用户',1);
            return $this->res(1,'添加失败！');
        }
        $this->opRec($adminId,'用户模块','添加用户',0);
        return $this->res(0,'添加成功！');
    }
    //编辑用户
    public function editUser(Request $request)
    {
        $adminId = $request->admin->userId;
        $userId = $request->userId;
        $data = $request->except(['userId','roles','sid','sexName']);

        $res = $this->updateData($this->admin_table,'userId',$userId,$data);
        if (!$res){
            $this->opRec($adminId,'用户模块','编辑用户',1);
            return $this->res(1,'更改失败！');
        }
        $this->opRec($adminId,'用户模块','编辑用户',0);
        return $this->res(0,'更改成功！');
    }
    //改变状态
    public function changeUserState(Request $request,$userId)
    {
        $adminId = $request->admin->userId;
        $state = $request->input('state');

        $res = $this->updateData($this->admin_table,'userId',$userId,['state'=>$state]);
        if (!$res){
            $this->opRec($adminId,'用户模块','编辑用户状态',1);
            return $this->res(1,'更改失败！');
        }
        $this->opRec($adminId,'用户模块','编辑用户状态',0);
        return $this->res(0,'更改成功！');
    }
    //删除用户
    public function deleteUser(Request $request,$userId)
    {
        $adminId = $request->admin->userId;
        $userIds = $request->data;

        if ($userId=='batch'){
            $res = $this->deletePatch($this->admin_table,'userId',$userIds);
        }else{
            $res = $this->deleteData($this->admin_table,'userId',$userId);
        }
        if (!$res){
            $this->opRec($adminId,'用户模块','删除用户',1);
            return $this->res(1,'删除失败！');
        }
        $this->opRec($adminId,'用户模块','删除用户',0);
        return $this->res(0, '删除成功！');
    }
    //导入用户
    public function importUser(Request $request)
    {
        $adminId = $request->admin->userId;
        $importData = $request->post('data');

        $num = 0;
        $temp = array();
        foreach ($importData as $value) {
            $temp['username'] = $value['0'];
            $temp['password'] = bcrypt($value['1']);
            $temp['nickname'] = $value['2'];
            $temp['trueName'] = $value['3'];
            $temp['phone'] = $value['4'];
            $temp['email'] = $value['5'];
            $temp['sex'] = $value['6']==='男'?1:0;
            $res = DB::table($this->admin_table)->updateOrInsert(['username'=>$temp['username']],$temp);
            if ($res){
                $num += 1;
            }
        }
        $this->opRec($adminId,'用户模块','导入用户数据',0);
        return $this->res(0,'共导入了'.$num.'条数据',$num);
    }
    //获取全部角色
    public function getRole()
    {
        $roles = $this->getData($this->role_table,'deleted','0',['roleId','roleName'])->all();
        if (!$roles){
            return $this->res(1,'没有数据！');
        }
        return $this->res(0,'SUCCESS','',$roles);
    }
}
