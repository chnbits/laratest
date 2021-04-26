<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Oper_record;
use Illuminate\Http\Request;

class BaseController extends Controller
{

    public function __construct(Request $request)
    {
        $this->ip = $request->ip();
        $this->method = $request->method();
        $this->path = $request->path();
        $this->parm = json_encode($request->all());
    }

    public function res($code,$msg,$count='',$data='')
    {
        return response()->json(['code'=>$code,'msg'=>$msg,'count'=>$count,'data'=>$data]);
    }

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
}
