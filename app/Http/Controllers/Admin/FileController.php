<?php

namespace App\Http\Controllers\Admin;


class FileController extends BaseController
{
    public function captcha()
    {
        $captcha = app('captcha')->create('flat',true);

        return $this->res(0,'SUCCESS','',$captcha);
    }
}
