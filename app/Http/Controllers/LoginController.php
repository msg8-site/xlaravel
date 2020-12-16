<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;



class LoginController extends Controller
{
    public function login(Request $request) {
		if(true===ZzAuth::check_auth(__CLASS__,__FUNCTION__,$msg)) {
			return redirect('/index');
		}else {
			return view('system/login');
		}
    }

    //登陆执行
	public function login_exec(Request $request) {
        $reqarr = $request->all();
		$validator = Validator::make($reqarr, [
            'username'  => ['bail', 'required', 'between:3,64', (new zd_alnum)],
            'password'  => ['bail', 'required', 'between:32,64', (new zd_alnum)],
            'randstr'   => ['bail', 'required', 'between:4,64', (new zd_alnum)],
            'checkcode' => ['bail', 'required', 'between:4,6', (new zd_alnum)],
        ]);
        if ($validator->fails()) {
            return cmd(400,'【错误】'.$validator->errors()->all()[0]);
		}
		$res = ZzAuth::check_login($reqarr,$showmsg);
		if(true!==$res) {
            return cmd(400,$showmsg);
		}else {
			return cmd(200,'登录成功'.$showmsg);
		}

	}
	
    //退出系统
	public function logout(Request $request) {
        $reqarr = $request->all();
		$validator = Validator::make($reqarr, [
            'logout' => 'bail|required|between:1,64|alpha_num',
        ]);
        if ($validator->fails()) {
			return cmd(400,'【错误】'.$validator->errors()->all()[0]);
		}
		$res = ZzAuth::logout();
		if(true!==$res) {
			return cmd(400,'【错误】退出失败，系统内部错误');
		}else {
			return cmd(200,'退出成功');
			return 'success';
		}

	}
    
    //图形验证码
	public function imgcodecreate() {
		//创建一个大小为 100*30 的验证码
		$image = imagecreatetruecolor(77, 25);
		$bgcolor = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $bgcolor);
		
		$captch_code = '';
		for ($i = 0; $i < 4; $i++) {
			$fontsize = 6;
			$fontcolor = imagecolorallocate($image, rand(0, 120), rand(0, 120), rand(0, 120));
			//$data = '1234567890';
			//$fontcontent = substr($data, rand(0, strlen($data) - 1), 1);
			$fontcontent = mt_rand(1,9);
			$captch_code .= $fontcontent;
			$x = ($i * 70 / 4) + rand(4, 8);
			$y = rand(2, 4);
			imagestring($image, $fontsize, $x, $y, $fontcontent, $fontcolor);
		}
        //就生成的验证码保存到session
        session([COMM_SYSFLAG.'_loginvcodetime' => time()]);
        session([COMM_SYSFLAG.'_loginvcodestr' => $captch_code]);
		
		//在图片上增加点干扰元素
		for ($i = 0; $i < 100; $i++) {
			$pointcolor = imagecolorallocate($image, rand(50, 200), rand(50, 200), rand(50, 200));
			imagesetpixel($image, rand(1, 99), rand(1, 29), $pointcolor);
		}
		
		//在图片上增加线干扰元素
		for ($i = 0; $i < 3; $i++) {
			$linecolor = imagecolorallocate($image, rand(80, 220), rand(80, 220), rand(80, 220));
			imageline($image, rand(1, 99), rand(1, 29), rand(1, 99), rand(1, 29), $linecolor);
		}
		//设置头
		header('content-type:image/png');
		imagepng($image);
		imagedestroy($image);
		
		
	}
}
