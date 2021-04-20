<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function captcha()
    {
        $captcha = app('captcha')->create('flat',true);

        $data = array(
            'code'=>0,
            'msg'=>'SUCCESS',
            'data'=>$captcha
        );

        return json_encode($data);
    }
}
