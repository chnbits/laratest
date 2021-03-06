<?php

namespace App\Http\Controllers\Admin;

use App\Models\Login_record;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends BaseController
{
    public function login(Request $request)
    {
        $username = $request->username;
        $password = $request->password;
        $captcha = $request->code;
        $captchaKey = $request->key;

        $validate = Auth::guard('admin')->attempt(['username'=>$username,'password'=>$password]);

        if (!$validate){
            return $this->res(1,'用户名或密码错误！');
        }

        if (!captcha_api_check($captcha,$captchaKey,'flat')){
            return $this->res(1,'验证码错误！');
        }

        $user = Auth::guard('admin')->user();
        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        if ($request->remeber_me){
            $token->expires_at = Carbon::now()->addWeeks(1);
        }
        $token->save();

        $uid = $user->userId;
        $ip = $request->ip();
        $device = $request->header('user-agent');

        $parm = array(
            'uid'=>$uid,
            'ip'=>$ip,
            'createTime'=>date('Y-m-d H:i:s',time()),
            'device'=>$device
        );
        Login_record::index($parm);

        if ($user['state']===1){
            return $this->res(1,'账号被禁用，请联系管理员！');
        }
        $res = array(
            'code'=>0,
            'msg'=>'登录成功！',
            'token_type'=>'Bearer',
            'access_token'=>$tokenResult->accessToken,
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        );
        return response()->json($res);
    }
}
