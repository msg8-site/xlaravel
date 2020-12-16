<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;


/**
 * @authcheckname=>
 */
class IndexController extends Controller
{

    /**
     * @authcheckname=>框架主页面
     * @authcheckshow=>1
     */
    public function index()
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return redirect('/login');
        } else {
            return view('system/index');
        }
    }

    /**
     * @authcheckname=>框架顶部栏
     * @authcheckshow=>1
     */
    public function iframeheader()
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        } else {
            return view('system/iframeheader');
        }
    }

    /**
     * @authcheckname=>框架左侧菜单
     * @authcheckshow=>1
     */
    public function iframemenu()
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        } else {
            $menulist = ZzAuth::menulist();
            return view('system/iframemenu', ['menulist' => $menulist]);
        }
    }

    /**
     * @authcheckname=>框架默认主页面
     * @authcheckshow=>1
     */
    public function iframebody()
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        } else {
            $dbres = DB::table('sys_user')->where('username',session(SESS_USERNAME, ''))->select('id','status','role','username','nickname','moreclientflag','moreclientkey','usergooglekey')->first();
            if(!empty($dbres)) {
                $roledbres = DB::table('sys_role')->where('id',($dbres->role??0))->first();
                if(!empty($roledbres)) {
                    $dbres->rolename = ($roledbres->rolename??'').'['.($dbres->role??0).']';
                }
            }
            $dbres->status_show    = comm_colorspan($dbres->status);
            $dbres->usergooglekey  = ('' == $dbres->usergooglekey) ? comm_spanstr('未绑定', 'orange') : comm_spanstr('已绑定', 'blue');
            $dbres->moreclientflag = ('1' == $dbres->moreclientflag) ? comm_spanstr('单终端', 'blue') : comm_spanstr('多终端', 'green');
            return view('system/iframebody',['userobj'=>$dbres]);
        }
    }

    //定时心跳保持session在线
    public function ajax_heartbeat()
    {
        return 'ok';
    }

    /**
     * @authcheckname=>用户密码修改
     * @authcheckshow=>1
     */
    public function passwordchange()
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        } else {
            return view('system/passwordchange');
        }
    }

    /**
     * @authcheckname=>用户密码修改-执行
     * @authcheckshow=>2
     */
    public function passwordchange_exec(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return cmd(400, $resmsg);
        }
        $reqarr = $request->all();
        $resarr = [];

        $validator = Validator::make($reqarr, [
            'oldpasswd' => ['bail', 'required', 'between:32,64', (new zd_alnum)],
            'newpasswd' => ['bail', 'required', 'between:32,64', (new zd_alnum)],
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $olddbobj = DB::table('sys_user')->where('username', session(SESS_USERNAME, ''))->first();
        if (empty($olddbobj)) {
            return cmd(400, '【错误】用户数据不存在，无法修改');
        } else if (($reqarr['oldpasswd'] ?? '') != ($olddbobj->passwd ?? '')) {
            return cmd(400, '【错误】原密码不正确');
        } else {
            $dbarr = [];
            $dbarr['update_datetime'] = date('Y-m-d H:i:s');
            $dbarr['passwd'] = $reqarr['newpasswd'];

            $resupd = DB::table('sys_user')->where('id', ($olddbobj->id ?? 0))->where('username', session(SESS_USERNAME, ''))->update($dbarr);
            if (!$resupd) {
                return cmd(400, '【错误】密码修改失败，系统内部错误');
            } else {
                ZzAuth::log_cudn('u', __CLASS__, __FUNCTION__, 'sys_user', ($olddbobj->id ?? 0), ZzAuth::data_diff($olddbobj, $dbarr, []));  //记录日志
                return cmd(200, '密码修改成功');
            }
        }
    }

    /**
     * @authcheckname=>用户动态码绑定
     * @authcheckshow=>1
     */
    public function googlecodebind(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];
        $userobj = DB::table('sys_user')->where('username', session(SESS_USERNAME, ''))->first();
        if (empty($userobj)) {
            return cmd(400, '【错误】用户数据不存在');
        } else {
            $bindflag = 'yes';
            if ('' != $userobj->usergooglekey) {
                $bindflag = 'yes';  //已绑定
            } else {
                $bindflag = 'no';
                //谷歌动态码验证
                $Google2FA = new Google2FA();
                $resarr['usergooglekey'] = $Google2FA->generateSecretKey(32);
                $resarr['showbindmsg'] = '';
                $resarr['showimgsrc'] = '';
                //谷歌动态码
                if (true === ctype_alnum($resarr['usergooglekey']) && strlen($resarr['usergooglekey']) >= 16 && strlen($resarr['usergooglekey']) <= 128) {
                    $urlencoded = 'otpauth://totp/' . urlencode(COMM_SYSNAME . '_' . $userobj->username . '_' . date('ymd')) . '?secret=' . $resarr['usergooglekey'] . '';
                    $resarr['showimgsrc'] = 'https://code.msg8.site/qrcode/index.php?text=' . urlencode($urlencoded);
                    $resarr['showbindmsg'] = '请使用对应APP扫描二维码完成绑定';
                } else {
                    $resarr['showbindmsg'] = '【错误】谷歌动态码数据生成失败，请稍后重试';
                }
            }
            $resarr['bindflag'] = $bindflag;
        }
        return view('system/googlecodebind', ['reqarr' => $reqarr, 'resarr' => $resarr]);
    }


    /**
     * @authcheckname=>用户动态码绑定-执行
     * @authcheckshow=>2
     */
    public function googlecodebind_exec(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return cmd(400, $resmsg);
        }
        $reqarr = $request->all();
        $resarr = [];

        $validator = Validator::make($reqarr, [
            'username'    => ['bail', 'required', 'between:3,64', (new zd_alnum)],
            'randbindkey' => ['bail', 'required', 'between:16,128', (new zd_alnum)],
            'googlecode'  => ['bail', 'required', 'between:4,6', (new zd_alnum)],
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $olddbobj = DB::table('sys_user')->where('username', session(SESS_USERNAME, ''))->first();
        if (empty($olddbobj)) {
            return cmd(400, '【错误】用户数据不存在');
        } else if (($reqarr['username'] ?? '') != $olddbobj->username) {
            return cmd(400, '【错误】用户数据不匹配');
        } else if ('' != $olddbobj->usergooglekey) {
            return cmd(400, '【错误】账号对应谷歌动态码已经绑定，不可再次绑定');
        } else {
            $rr = ZzAuth::funchk_gcodecheck(__FUNCTION__, $reqarr['randbindkey'], $reqarr['googlecode'], session(SESS_USERNAME, ''));
            if (true !== $rr) {
                return cmd(400, '【错误】' . $rr);
            } else {
                //通过验证码判断
                $dbarr = [];
                $dbarr['usergooglekey'] = $reqarr['randbindkey'];
                $resupd = DB::table('sys_user')->where('username', session(SESS_USERNAME, ''))->where('usergooglekey', '')->update($dbarr);
                if (!$resupd) {
                    return cmd(400, '【错误】绑定操作失败，系统错误');
                } else {
                    ZzAuth::log_cudn('u', __CLASS__, __FUNCTION__, 'sys_user', ($olddbobj->id ?? 0), ZzAuth::data_diff($olddbobj, $dbarr, []));  //记录日志
                    return cmd(200, '动态码绑定成功');
                }
            }
        }
    }
}
