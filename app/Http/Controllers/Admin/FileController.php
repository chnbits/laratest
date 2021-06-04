<?php
namespace App\Http\Controllers\Admin;

use Exception;
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
        $position = !empty($directory)?$directory:null;

        $exist = DB::table($this->file_table)->where('directory',$position)->where('name',$name)->exists();
        if ($exist){
            return $this->res(1,'文件'.$name.'已存在，上传失败！');
        }

        $path = $request->file('file')->store($position,'public');
        $res = $this->makeUpload($name,$size,$extension,$position,$path);

        if (!$res){
            return $this->res(1,'文件'.$name.'上传失败！');
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
            $position = !empty($dir)?$dir:null;

            $exist = DB::table($this->file_table)->where('directory',$position)->where('name',$name)->exists();
            if ($exist){
                return $this->res(1,'文件'.$name.'已存在，上传失败！');
            }

            $path = $file->store($position,'public');
            $res = $this->makeUpload($name,$size,$extension,$position,$path);
            if (!$res){
                return $this->res(1,'文件'.$name.'上传失败！');
            }
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
            $files = DB::table($this->file_table)->where('directory',null)->orderBy($sort,$order)->get()->all();
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
            return $this->res(1,'请输入文件夹名！');
        }
        File::makeDirectory(public_path().'/files/'.$directory.'/'.$file_name, 777, true, true);
        File::makeDirectory(public_path().'/thumb/'.$directory.'/'.$file_name, 777, true, true);
        try {
            $this->insertData($this->file_table,['isDirectory'=>1,'name'=>$file_name,'directory'=>$directory]);
        }catch (Exception $e){
            if ($e->getCode()==23000){
                return $this->res(1,$file_name.'文件夹已存在,添加失败！');
            }
        }
        return $this->res(0,'添加成功！');
    }

    //封装上传接口
    public function makeUpload($name,$size,$extension,$position,$path)
    {
        $path_arr = explode('.',$path);
        $newName = $path_arr[0];
        $file_path = public_path().'/thumb/'.$position;
        if (!File::exists($file_path)) {
            File::makeDirectory($file_path, 777, true, true);
        }

        $ext = array('jpg','png','bmp','jpeg');
        if (in_array($extension,$ext)){
            Image::make(public_path().'/files/'.$path)->heighten(200)->save(public_path().'/thumb/'.$newName.'-thumb.'.$extension);
            $data['thumbnail'] = 'thumb/'.$newName.'-thumb.'.$extension;
        }
        $data['name'] = $name;
        $data['length'] = $size;
        $data['url'] = 'files/'.$path;
        $data['directory'] = $position;
        return $this->insertData($this->file_table,$data);
    }

    //删除文件和文件夹
    public function deleteFile(Request $request)
    {
        $path_arr = array();
        $thumb_arr = array();
        $files = $request->all();
        foreach ($files as $file)
        {
            $isDir = $file['isDirectory'];
            $name = $file['name'];
            $url = $file['url'];
            $thumb = $file['thumbnail'];
            $path = str_replace(env('APP_URL'),'',$url);
            $thumb_path = str_replace(env('APP_URL'),'',$thumb);
            $dir = $file['directory'];

            if ($isDir === 1){
                $exist = File::exists(public_path().'/files/'.$dir.'/'.$name);
                if (!$exist){
                    return $this->res(1,'文件夹 '.$name.' 不存在！');
                }
                try {
                    rmdir(public_path().'/files/'.$dir.'/'.$name);
                }catch (Exception $e){
                    if($e->getCode() === 0 ){
                        return $this->res(1,'请先删除'.$name.'文件夹下所有文件后重试');
                    }
                }
                //删除thumb文件夹
                rmdir(public_path().'/thumb/'.$dir.'/'.$name);

                DB::table($this->file_table)->where('directory','REGEXP',$dir?$dir.'/'.$name:$name)->delete();
                $this->forceDelete($this->file_table,'name',$name);
            }else{
                $path_arr[] = $path;
                if (File::exists($thumb_path)){
                   $thumb_arr[] = $thumb_path;
                }
                $db_res = $this->forceDelete($this->file_table,'url',$path);
                if (!$db_res){
                    $this->res(1,'删除失败！');
                }
            }
        }
        $file_res = File::delete($path_arr);
        $thumb_res = File::delete($thumb_arr);
        if (!$file_res || !$thumb_res){
            return $this->res(1,'删除失败！');
        }
        return $this->res(0,'删除成功！');
    }
    /*获取图标元素*/
    public function getIcons()
    {
        return  file_get_contents(public_path().'/icons/icons.json');
    }
}
