<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends BaseController
{
    protected $admin_table = 'admins';
    protected $role_table = 'roles';
    protected $menu_table = 'menus';
    protected $dict_table = 'dict';
    protected $dictData_table = 'dict_data';

    //查询用户
    public function getUserPage(Request $request)
    {
        $limit = $request->get('limit');
        $username = $request->get('username');
        $nickname = $request->get('nickname');
        $sex = $request->get('sex');

        $res = DB::table($this->admin_table)
            ->select('userId','roleIds','username','nickname','phone','admins.state','dictDataValue as sex','dictDataName as sexName','admins.createTime')
            ->leftJoin('dict_data',function ($join){
                $join->on('admins.sex','=','dict_data.dictDataValue')
                    ->where('dict_data.dictId','=',1);
            })
            ->where('admins.deleted',0)
            ->where('username','like','%'.$username.'%')
            ->where('nickname','like','%'.$nickname.'%')
            ->where('sex','like','%'.$sex.'%')
            ->paginate($limit);

        $users = $res->items();
        $count = $res->total();
        if ($count == 0){
            return $this->res(1,'没有数据！');
        }

        $roles = DB::table($this->role_table)->get(['roleId','roleName'])->all();
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

        foreach ($user_data as $value)
        {
            $roleId_arr = json_decode($value->roleIds,true);
            foreach ($roleId_arr as $v)
            {
                $user_data[$value->userId]->roles[] = $role_data[$v];
            }
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

        $res = DB::table($this->admin_table)->insert($data);
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
        $data['updateTime'] = date('Y-m-d H:i:s',time());

        $res = DB::table($this->admin_table)->where('userId',$userId)->update($data);
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
        $data['state'] = $request->state;
        $data['updateTime'] = date('Y-m-d H:i:s',time());

        $res = DB::table($this->admin_table)->where('userId',$userId)->update($data);
        if (!$res){
            $this->opRec($adminId,'用户模块','编辑用户状态',1);
            return $this->res(1,'更改失败！');
        }
        $this->opRec($adminId,'用户模块','编辑用户状态',0);
        return response()->json([
            'code'=>0,
            'msg'=>'更改成功！',
            'state'=>$data['state']
        ]);
    }
    //删除用户
    public function deleteUser(Request $request,$userId)
    {
        $adminId = $request->admin->userId;
        $userIds = $request->data;

        if ($userId=='batch'){
            $res = DB::table($this->admin_table)->whereIn('userId',$userIds)->update(['deleted'=>1]);
        }else{
            $res = DB::table($this->admin_table)->where('userId',$userId)->update(['deleted'=>1]);
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
        $roles = DB::table($this->role_table)->where('deleted','0')->get()->all();
        if (!$roles){
            return $this->res(1,'没有数据！');
        }
        return $this->res(0,'SUCCESS','',$roles);
    }
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

    //菜单管理
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
                ->Where('path','like','%'.$path.'%')
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

//    字典管理
//    获取字典项
    public function getDict()
    {
        $dict = DB::table($this->dict_table)
            ->where('deleted',0)
            ->orderBy('sortNumber','asc')
            ->get()->all();
        if (!$dict){
            return $this->res(1,'暂时没有数据！');
        }
        return $this->res(0,'SUCCESS','',$dict);
    }
//    获取字典值
    public function getDictPage(Request $request)
    {
        $limit = $request->get('limit');
        $dictId = $request->get('dictId');

        $res = DB::table($this->dictData_table)
            ->where('dictId',$dictId)
            ->where('deleted',0)
            ->orderBy('sortNumber','asc')
            ->paginate($limit);
        $dictData = $res->items();
        $count = $res->total();
        if ($count == 0){
            return $this->res(1,'没有数据！');
        }
        return $this->res(0,'SUCCESS',$count,$dictData);
    }
//    创建和更改字典
    public function createDict(Request $request)
    {
        $adminId = $request->admin->userId;
        $baseData = $request->except('dictId');
        $createData['createTime'] = date('Y-m-d H:i:s',time());
        $updateData['updateTime'] = date('Y-m-d H:i:s',time());

        $data = $request->post('dictId')?array_merge($baseData,$updateData):array_merge($baseData,$createData);

        $res = DB::table($this->dict_table)->updateOrInsert(['dictId'=>$request->dictId],$data);

        if (!$res){
            $this->opRec($adminId,'字典模块','添加或修改字典',1);
            return $this->res(1,'操作失败！');
        }
        $this->opRec($adminId,'字典模块','添加或修改字典',0);
        return $this->res(0,'操作成功！');
    }
//    删除字典
    public function deleteDict(Request $request,$id)
    {
        $adminId = $request->admin->userId;
        $table = $this->dict_table;
        $column = 'dictId';
        $res = $this->deleteData($table,$column,$id);

        if (!$res){
            $this->opRec($adminId,'字典模块','删除字典项',1);
            return $this->res(1,'删除失败！');
        }
        $this->opRec($adminId,'字典模块','删除字典项',0);
        return $this->res(0,'删除成功！');
    }
//    创建更改字典项
    public function createDictData(Request $request)
    {
        $adminId = $request->admin->userId;
        $baseData = $request->except('dictDataId');
        $createData['createTime'] = date('Y-m-d H:i:s',time());
        $updateData['updateTime'] = date('Y-m-d H:i:s',time());
        $data = $request->post('dictDataId')?array_merge($baseData,$updateData):array_merge($baseData,$createData);

        $res = DB::table($this->dictData_table)->updateOrInsert(['dictDataId'=>$request->dictDataId],$data);

        if (!$res){
            $this->opRec($adminId,'字典模块','添加或修改字典值',1);
            return $this->res(1,'操作失败！');
        }
        $this->opRec($adminId,'字典模块','添加或修改字典值',0);
        return $this->res(0,'操作成功！');
    }
    //    删除字典项
    public function deleteDictData(Request $request,$id)
    {
        $adminId = $request->admin->userId;
        $table = $this->dictData_table;
        $column = 'dictDataId';
        $res = $this->deleteData($table,$column,$id);
        if (!$res) {
            $this->opRec($adminId,'字典模块','删除字典项值',1);
            return $this->res(1,'删除失败！');
        }
        $this->opRec($adminId,'字典模块','删除字典项值',0);
        return $this->res(0,'删除成功！');
    }

    //删除数据
    public function deleteData($table,$column,$id)
    {
        $res = DB::table($table)->where($column, $id)->update(['deleted' => 1, 'updateTime' => date('Y-m-d H:i:s', time())]);
        return $res;
    }
    //登录记录
    public function loginRecord(Request $request)
    {
        $limit = $request->limit;
        $username = $request->username;
        $start = $request->createTimeStart;
        $end = $request->createTimeEnd;
        $loginRecord = DB::table('login_record')
            ->join('admins','uid','=','userId')
            ->select(['username','nickname','ip','device','operType','login_record.createTime'])
            ->where('admins.username','like','%'.$username.'%')
            ->whereBetween('login_record.createTime',[$start,$end])
            ->orderBy('login_record.createTime','DESC')
            ->paginate($limit);

        $count = $loginRecord->total();

        return $this->res(0,'SUCCESS',$count,$loginRecord->items());
    }
    //操作记录
    public function oplog(Request $request)
    {
        $limit = $request->limit;
        $username = $request->username;
        $model = $request->model;
        $start = $request->createTimeStart;
        $end = $request->createTimeEnd;

        $operRecord = DB::table('oper_record')
            ->select(['username','nickname','ip','url','requestMethod','model','description','param','oper_record.state','oper_record.createTime'])
            ->join('admins','oper_record.userId','=','admins.userId')
            ->where('admins.username','like','%'.$username.'%')
            ->where('model','like','%'.$model.'%')
            ->whereBetween('oper_record.createTime',[$start,$end])
            ->orderBy('oper_record.createTime','DESC')
            ->paginate($limit);

        $count = $operRecord->total();

        return $this->res(0,'SUCCESS',$count,$operRecord->items());
    }
}
