<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizeController extends BaseController
{
    //获取组织列表
    public function getOrganize()
    {
        $organize = DB::table($this->org_table)
            ->where('deleted',0)
            ->orderBy('sortNumber','ASC')
            ->get()->all();
        if (!$organize){
            return $this->res(1,'暂时没有数据！');
        }
        return $this->res(0,'SUCCESS','',$organize);
    }
    //获取字典值
    public function getDictData(Request $request)
    {
        $dictCode = $request->get('dictCode');
        $dictData = DB::table($this->dict_table)
            ->where('dictCode',$dictCode)
            ->join($this->dictData_table,'dict_data.dictId','=','dict.dictId')
            ->get(['dictDataId','dictDataName'])->all();
        if (!$dictData){
            return $this->res(1,'暂时没有数据！');
        }
        return $this->res(0,'SUCCESS','',$dictData);
    }
    //创建和更改组织
    public function createOrganize(Request $request)
    {
        $adminId = $request->admin->userId;
        $baseData = $request->except('organizationId','parentName','children');
        $createData['createTime'] = date('Y-m-d H:i:s',time());
        $updateData['updateTime'] = date('Y-m-d H:i:s',time());
        $baseData['parentId'] = $request->post('parentId',0);

        $data = $request->post('dictId')?array_merge($baseData,$updateData):array_merge($baseData,$createData);

        $res = DB::table($this->org_table)->updateOrInsert(['organizationId'=>$request->organizationId],$data);

        if (!$res){
            $this->opRec($adminId,'组织模块',$data,'添加或修改组织',1);
            return $this->res(1,'操作失败！');
        }
        $this->opRec($adminId,'组织模块',$data,'添加或修改组织',0);
        return $this->res(0,'操作成功！');
    }
    //删除机构
    public function deleteOrganize(Request $request,$organizationId)
    {
        $adminId = $request->admin->userId;

        $res = $this->deleteData($this->org_table,'organizationId',$organizationId);
        if (!$res) {
            $this->opRec($adminId,'组织模块',$organizationId,'删除组织',1);
            return $this->res(1,'删除失败！');
        }
        $this->opRec($adminId,'组织模块',$organizationId,'删除组织',0);
        return $this->res(0,'删除成功！');
    }
}
