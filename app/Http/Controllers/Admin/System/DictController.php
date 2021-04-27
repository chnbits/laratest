<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DictController extends BaseController
{
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

}
