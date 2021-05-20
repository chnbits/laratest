<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class FileController extends BaseController
{
    //t图形验证码
    public function captcha()
    {
        $captcha = app('captcha')->create('flat',true);

        return $this->res(0,'SUCCESS','',$captcha);
    }
    //上传文件
    public function upload(Request $request)
    {
        $dir = $request->input('directory');
        $directory = str_replace(',','/',$dir);

        $name = $request->file('file')->getClientOriginalName();
        $extension = $request->file('file')->extension();
        $size = $request->file('file')->getSize();

        $position = !empty($directory)?$directory:date('Ymd');

        $path = $request->file('file')->store($position,'public');
        $path_arr = explode('.',$path);
        $newName = $path_arr[0];

        $date_path = public_path().'/thumb/'.$position;

        if (!File::exists($date_path)) {
            $file_data = array(
                'isDirectory'=>1,
                'name'=>date('Ymd'),
            );
            File::makeDirectory($date_path, 777, true, true);
            $this->insertData($this->file_table,$file_data);
        }
        $ext = array('jpg','png','bmp','jpeg');
        if (in_array($extension,$ext)){
            Image::make(public_path().'/files/'.$path)->heighten(200)->save(public_path().'/thumb/'.$newName.'-thumb.'.$extension);
            $data['thumbnail'] = '/thumb/'.$newName.'-thumb.'.$extension;
        }
        $data['name'] = $name;
        $data['length'] = $size;
        $data['url'] = 'files/'.$path;
        $data['directory'] = $position;
        $res = $this->insertData($this->file_table,$data);
        if (!$res){
            return $this->res(1,'上传失败！');
        }
        return $this->res(0,'上传成功！');
    }
    //获取文件列表
    public function getFileList(Request $request)
    {
        $directory = $request->input('directory');

        $sort = $request->input('sort')?$request->input('sort'):'createTime';
        $order = $request->input('order')?$request->input('order'):'DESC';

        if (empty($directory)){
            $files = DB::table($this->file_table)->where('isDirectory',1)->where('directory',null)->orderBy($sort,$order)->get()->all();
        }else{
            $files = DB::table($this->file_table)->where('directory',$directory)->orderBy($sort,$order)->get()->all();
        }
        return $this->res(0,'','',$files);
    }
    //创建目录
    public function createFile(Request $request)
    {
        $file_name = $request->input('filename');
        $directory = $request->input('path');

        if (empty($file_name)){
            return $this->res(1,'请输入文件名！');
        }
        File::makeDirectory(public_path().'/files/'.$directory.'/'.$file_name, 777, true, true);
        File::makeDirectory(public_path().'/thumb/'.$directory.'/'.$file_name, 777, true, true);
        $res = $this->insertData($this->file_table,['isDirectory'=>1,'name'=>$file_name,'directory'=>$directory]);
        if (!$res){
            return $this->res(1,'添加失败！');
        }
        return $this->res(0,'添加成功！');
    }
}
