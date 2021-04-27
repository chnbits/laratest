<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends BaseController
{
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
            ->orWhereBetween('login_record.createTime',[$start,$end])
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
            ->orWhereBetween('oper_record.createTime',[$start,$end])
            ->orderBy('oper_record.createTime','DESC')
            ->paginate($limit);

        $count = $operRecord->total();

        return $this->res(0,'SUCCESS',$count,$operRecord->items());
    }
}
