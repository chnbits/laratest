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

        $exist = DB::table($this->file_table)->where('directory',$directory)->where('name',$name)->exists();
        if ($exist){
            return $this->res(1,'文件 '.$name.' 已存在！');
        }
        $res = $this->makeUpload($name,$size,$extension,$position,$path);

        if (!$res){
            return $this->res(1,'上传失败！');
        }
        return $this->res(0,'上传成功！');
    }
    //批量上传文件
    public function uploads(Request $request)
    {
        $files = $request->allFiles();
        $dir = $request->input('path');
        foreach ($files as $file){
            $extension = $file->extension();
            $name = $file->getClientOriginalName();
            $size = $file->getSize();
            $position = !empty($dir)?$dir:date('Ymd');
            $path = $file->store($position,'public');
            $exist = DB::table($this->file_table)->where('directory',$dir)->where('name',$name)->exists();
            if ($exist){
                return $this->res(1,'文件 '.$name.' 已存在！请删除后再上传');
            }
            $this->makeUpload($name,$size,$extension,$position,$path);
        }

        return $this->res(0,'上传完成！');
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

        $exist = DB::table($this->file_table)->where('directory',$directory)->where('name',$file_name)->exists();
        if ($exist){
            return $this->res(1,'文件夹已存在！');
        }
        if (empty($file_name)){
            return $this->res(1,'请输入文件夹名！');
        }
        File::makeDirectory(public_path().'/files/'.$directory.'/'.$file_name, 777, true, true);
        File::makeDirectory(public_path().'/thumb/'.$directory.'/'.$file_name, 777, true, true);
        $res = $this->insertData($this->file_table,['isDirectory'=>1,'name'=>$file_name,'directory'=>$directory]);
        if (!$res){
            return $this->res(1,'添加失败！');
        }
        return $this->res(0,'添加成功！');
    }

    //封装上传接口
    public function makeUpload($name,$size,$extension,$position,$path)
    {
        $path_arr = explode('.',$path);
        $newName = $path_arr[0];

        $date_path = public_path().'/thumb/'.$position;

        if (!File::exists($date_path)) {
            $file_data = array(
                'isDirectory'=>1,
                'name'=>date('Ymd'),
            );
            File::makeDirectory($date_path, 777, true, true);
            $exist = DB::table($this->file_table)->where('directory',null)->where('name',date('Ymd'))->exists();
            if (!$exist){
                $this->insertData($this->file_table,$file_data);
            }
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
        return $res;
    }

    //删除文件和文件夹
    public function deleteFile(Request $request)
    {
        $files = $request->all();
        foreach ($files as $file)
        {
            $isDir = $file['isDirectory'];
            $name = $file['name'];
            $dir = $file['directory'];
            if ($isDir === 1){
                DB::table($this->file_table)->where('directory','REGEXP',$dir?$dir.'/'.$name:$name)->delete();
                $this->forceDelete($this->file_table,'name',$name);
            }else{
                $this->forceDelete($this->file_table,'name',$name);
            }
        }
    }
}
