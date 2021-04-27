<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Oper_record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{

    protected $admin_table = 'admins';
    protected $role_table = 'roles';
    protected $menu_table = 'menus';
    protected $dict_table = 'dict';
    protected $dictData_table = 'dict_data';

    public function __construct(Request $request)
    {
        $this->ip = $request->ip();
        $this->method = $request->method();
        $this->path = $request->path();
        $this->parm = json_encode($request->all());
    }

    //操作记录
    public function opRec($userId,$model,$description,$state='0')
    {
        $parms = array(
            'userId'=>$userId,
            'ip'=>$this->ip,
            'requestMethod'=>$this->method,
            'url'=>$this->path,
            'param'=>$this->parm,
            'model'=>$model,
            'description'=>$description,
            'state'=>$state,
            'createTime'=>date('Y-m-d H:i:s',time())
        );
        Oper_record::index($parms);
    }
    //查询数据
    public function getData($table,$column,$value,$key='*')
    {
        $res = DB::table($table)->where($column,$value)->get($key);
        return $res;
    }
    //插入数据
    public function insertData($table,$data)
    {
        $data['createTime'] = date('Y-m-d H:i:s',time());
        $res = DB::table($table)->insert($data);
        return $res;
    }
    //更新数据
    public function updateData($table,$column,$value,$data)
    {
        $data['updateTime'] = date('Y-m-d H:i:s', time());
        $res = DB::table($table)->where($column, $value)->update($data);
        return $res;
    }
    //删除单个数据
    public function deleteData($table,$column,$value)
    {
        $res = DB::table($table)->where($column, $value)->update(['deleted' => 1, 'updateTime' => date('Y-m-d H:i:s', time())]);
        return $res;
    }
    //删除多行数据
    public function deletePatch($table,$column,$values)
    {
        $res = DB::table($table)->whereIn($column,$values)->update(['deleted'=>1,'updateTime' => date('Y-m-d H:i:s', time())]);
        return $res;
    }

    //返回结果
    public function res($code,$msg,$count='',$data='')
    {
        return response()->json(['code'=>$code,'msg'=>$msg,'count'=>$count,'data'=>$data]);
    }
}
