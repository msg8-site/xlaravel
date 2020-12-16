<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;

/**
 * @authcheckname=>用户
 */
class UserController extends Controller
{
    private $tablename = 'sys_user';
    private $viewarray = [];

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'index'  => 'system/user_index',
            'add'    => 'system/user_add',
            'update' => 'system/user_update',
            'delete' => 'system/user_delete',
        ];
    }


    /**
     * @authcheckname=>数据查看
     * @authcheckshow=>1
     */
    public function index(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['reqarr' => $reqarr, 'resarr' => $resarr]);
    }

    /**
     * @authcheckname=>数据查看-返回
     * @authcheckshow=>2
     */
    public function index_tabledata(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return ['code' => 9, 'msg' => $resmsg, 'data' => []];
        }
        $reqarr = $request->all();
        $resarr = [];
        $validator = Validator::make($reqarr, [
            'username' => ['bail', 'nullable', 'between:1,64', (new zd_alnum)],
            'nickname' => 'bail|nullable|between:1,64',
            'status'   => 'bail|nullable|integer',
            'page'     => 'bail|nullable|integer',
            'limits'   => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table($this->tablename)->select('id', 'status', 'role', 'username', 'nickname', 'useremail', 'userphone', 'usergooglekey', 'create_datetime', 'update_datetime', 'moreclientflag', 'moreclientkey', 'backup1', 'backup2', 'backup3');
        if ('' == ($reqarr['status'] ?? '')) {
            $DB->whereIn('status', [1, 2]);
        } else if (is_numeric($reqarr['status'] ?? '')) {
            $DB->where('status', ($reqarr['status'] ?? '0'));
        }
        if ('' != ($reqarr['username'] ?? '')) {
            $DB->where('username', 'like', '%' . ($reqarr['username'] ?? '') . '%');
        }
        if ('' != ($reqarr['nickname'] ?? '')) {
            $DB->where('nickname', 'like', '%' . ($reqarr['nickname'] ?? '') . '%');
        }

        $dbcount = $DB->count();
        $dbobj   = $DB->orderBy('status')->paginate($reqarr['limit'] ?? 20)->items();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
            $rolecheckarr = [];
            $roleobj = DB::table('sys_role')->get();
            if (!empty($roleobj)) {
                foreach ($roleobj as $valr) {
                    if (($valr->id ?? 0) > 0) {
                        $rolecheckarr[($valr->id ?? 0)] = $valr->rolename;
                    }
                }
            }
            foreach ($dbobj as $valb) {
                $valb->status_show    = comm_colorspan($valb->status);
                $valb->usergooglekey  = ('' == $valb->usergooglekey) ? comm_spanstr('未绑定', 'orange') : comm_spanstr('已绑定', 'blue');
                $valb->moreclientflag = ('1' == $valb->moreclientflag) ? comm_spanstr('单终端', 'blue') : comm_spanstr('多终端', 'green');
                $valb->rolename       = ($rolecheckarr[($valb->role ?? 0)] ?? '') . '[' . $valb->role . ']';
            }

            return ['code' => 0, 'msg' => '查询成功', 'count' => $dbcount, 'data' => $dbobj];
        }
    }

    /**
     * @authcheckname=>数据添加
     * @authcheckshow=>1
     */
    public function add(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];

        $rolecheckarr = [];
        $rolecheckarr['0'] = '请选择';
        $roleobj = DB::table('sys_role')->get();
        if (!empty($roleobj)) {
            foreach ($roleobj as $valr) {
                if (($valr->id ?? 0) > 0) {
                    $rolecheckarr[($valr->id ?? 0)] = $valr->rolename . ' [' . ($valr->id ?? 0) . ']';
                }
            }
        }
        $resarr['option_status']         = comm_ccoption('', 'typestatus', false);
        $resarr['option_role']           = comm_ccoption('', $rolecheckarr, false);
        $resarr['option_moreclientflag'] = comm_ccoption('', 'moreclient', false);

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['reqarr' => $reqarr, 'resarr' => $resarr]);
    }
    /**
     * @authcheckname=>数据添加-执行
     * @authcheckshow=>2
     */
    public function add_exec(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return cmd(400, $resmsg);
        }
        $reqarr = $request->all();
        $resarr = [];

        $validator = Validator::make($reqarr, [
            'username'       => ['bail', 'required', 'between:3,64', (new zd_alnum)],
            'role'           => 'bail|required|integer',
            'status'         => 'bail|required|integer',
            'nickname'       => 'bail|required|between:1,64',
            'moreclientflag' => 'bail|required|integer',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $dbobj = DB::table($this->tablename)->where('username', ($reqarr['username'] ?? ''))->first();
        if (!empty($olddbobj)) {
            return cmd(400, '【错误】用户名已经存在，不可重复添加');
        } else {
            $errmsg = '';
            $dbarr = [];
            $dbarr['username']        = $reqarr['username'] ?? '';
            $dbarr['role']            = $reqarr['role'] ?? '0';
            $dbarr['status']          = $reqarr['status'] ?? '0';
            $dbarr['nickname']        = $reqarr['nickname'] ?? '';
            $dbarr['useremail']       = $reqarr['useremail'] ?? '';
            $dbarr['userphone']       = $reqarr['userphone'] ?? '';
            $dbarr['moreclientflag']  = $reqarr['moreclientflag'] ?? '0';
            $dbarr['usergooglekey']   = '';
            $dbarr['backup1']         = $reqarr['backup1'] ?? '';
            $dbarr['backup2']         = $reqarr['backup2'] ?? '';
            $dbarr['backup3']         = $reqarr['backup3'] ?? '';
            $dbarr['create_datetime'] = date('Y-m-d H:i:s');

            //重置用户密码
            $newrandpasswd = comm_randpasswd();
            $dbarr['passwd'] =  hash('sha256', hash('sha256', hash('sha256', $newrandpasswd)));
            $errmsg .= '<br>【注意】<br>';
            $errmsg .= '用户初始账号密码如下：<br>';
            $errmsg .= '账号:' . $dbarr['username'] . '<br>';
            $errmsg .= '密码:' . $newrandpasswd . '<br>';
            $errmsg .= '请保存账号密码后再关闭本弹窗<br>';

            $resinid = DB::table($this->tablename)->insertGetId($dbarr);
            if (!$resinid) {
                return cmd(400, '【错误】数据添加失败，系统错误');
            } else {
                ZzAuth::log_cudn('c', __CLASS__, __FUNCTION__, $this->tablename, $resinid, ZzAuth::data_tojstr($dbarr, []));  //记录日志
                return cmd(200, '数据添加成功' . $errmsg);
            }
        }
    }


    /**
     * @authcheckname=>数据修改
     * @authcheckshow=>1
     */
    public function update(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];

        $resarr['errmsg'] = '';  //不为空时页面直接仅显示本信息
        if (!is_numeric($reqarr['id'] ?? '')) {
            $resarr['errmsg'] = '【错误】非法操作，未匹配到对应数据';
        } else {
            $DB = DB::table($this->tablename)->select('id', 'status', 'role', 'username', 'nickname', 'useremail', 'userphone', 'usergooglekey', 'create_datetime', 'update_datetime', 'moreclientflag', 'moreclientkey', 'backup1', 'backup2', 'backup3');
            $dbobj = $DB->where('id', ($reqarr['id'] ?? '0'))->first();
            if (empty($dbobj)) {
                $resarr['errmsg'] = '【错误】未找到对应数据';
            } else {
                $rolecheckarr = [];
                $rolecheckarr['0'] = '请选择';
                $roleobj = DB::table('sys_role')->get();
                if (!empty($roleobj)) {
                    foreach ($roleobj as $valr) {
                        if (($valr->id ?? 0) > 0) {
                            $rolecheckarr[($valr->id ?? 0)] = $valr->rolename . ' [' . ($valr->id ?? 0) . ']';
                        }
                    }
                }

                $resarr['data']                          = get_object_vars($dbobj);
                $resarr['data']['option_status']         = comm_ccoption($resarr['data']['status'], 'typestatus', false);
                $resarr['data']['option_role']           = comm_ccoption($resarr['data']['role'], $rolecheckarr, false);
                $resarr['data']['option_moreclientflag'] = comm_ccoption($resarr['data']['moreclientflag'], 'moreclient', false);
            }
        }

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['reqarr' => $reqarr, 'resarr' => $resarr]);
    }

    /**
     * @authcheckname=>数据修改-执行
     * @authcheckshow=>2
     */
    public function update_exec(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return cmd(400, $resmsg);
        }
        $reqarr = $request->all();
        $resarr = [];

        $validator = Validator::make($reqarr, [
            'id'             => 'bail|required|integer',
            'role'           => 'bail|required|integer',
            'status'         => 'bail|required|integer',
            'nickname'       => 'bail|required|between:1,64',
            'moreclientflag' => 'bail|required|integer',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $olddbobj = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? 0))->first();
        if (empty($olddbobj)) {
            return cmd(400, '【错误】数据不存在，无法修改');
        } else {
            $errmsg = '';
            $dbarr = [];
            $dbarr['role']            = $reqarr['role'] ?? '0';
            $dbarr['status']          = $reqarr['status'] ?? '0';
            $dbarr['nickname']        = $reqarr['nickname'] ?? '';
            $dbarr['useremail']       = $reqarr['useremail'] ?? '';
            $dbarr['userphone']       = $reqarr['userphone'] ?? '';
            $dbarr['moreclientflag']  = $reqarr['moreclientflag'] ?? '0';
            $dbarr['backup1']         = $reqarr['backup1'] ?? '';
            $dbarr['backup2']         = $reqarr['backup2'] ?? '';
            $dbarr['backup3']         = $reqarr['backup3'] ?? '';
            $dbarr['update_datetime'] = date('Y-m-d H:i:s');
            if ('root' == $olddbobj->username ?? '') {
                $dbarr['status'] = '1';
                $dbarr['nickname'] = '超级管理员';
            }
            if ('on' == ($reqarr['resetpasswd'] ?? '')) {
                if ('root' == $olddbobj->username ?? '') {
                    $errmsg .= '<br>【注意】<br>';
                    $errmsg .= 'root超级管理员账号受系统保护，不支持密码重置操作<br>';
                } else {
                    //重置用户密码
                    $newrandpasswd = comm_randpasswd();
                    $dbarr['passwd'] =  hash('sha256', hash('sha256', hash('sha256', $newrandpasswd)));
                    $errmsg .= '<br>【注意】<br>';
                    $errmsg .= '用户密码触发重置操作，重置后密码为：<br>';
                    $errmsg .= $newrandpasswd . '<br>';
                    $errmsg .= '请保存密码后再关闭本弹窗<br>';
                }
            }
            if ('on' == ($reqarr['resetusergooglekey'] ?? '')) {
                //重置谷歌动态码密钥
                $dbarr['usergooglekey'] = '';
                $errmsg .= '<br>【注意】<br>';
                $errmsg .= '谷歌绑定密钥已经清除，请联系用户后台重新绑定<br>';
            }
            $resupd = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? 0))->update($dbarr);
            if (!$resupd) {
                return cmd(400, '【错误】数据修改失败，系统错误');
            } else {
                ZzAuth::log_cudn('u', __CLASS__, __FUNCTION__, $this->tablename, ($reqarr['id'] ?? 0), ZzAuth::data_diff($olddbobj, $dbarr, []));  //记录日志
                return cmd(200, '数据修改成功' . $errmsg);
            }
        }
    }

    /**
     * @authcheckname=>数据删除-执行
     * @authcheckshow=>2
     */
    public function delete(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return cmd(400, $resmsg);
        }
        $reqarr = $request->all();
        $resarr = [];

        $validator = Validator::make($reqarr, [
            'id' => 'bail|required|integer',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $olddbobj = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? 0))->first();
        if (empty($olddbobj)) {
            return cmd(400, '【错误】数据不存在，无法删除');
        } else {
            if ('root' == $olddbobj->username ?? '') {
                return cmd(400, '【错误】root账号受系统保护，无法删除');
            } else if ('1' == $olddbobj->status ?? '') {
                return cmd(400, '【错误】状态为开启的数据不可删除');
            } else {
                $resdel = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? 0))->delete();
                if (!$resdel) {
                    return cmd(400, '【错误】数据删除失败，系统错误');
                } else {
                    //记录修改日志
                    ZzAuth::log_cudn('d', __CLASS__, __FUNCTION__, $this->tablename, ($reqarr['id'] ?? 0), ZzAuth::data_tojstr($olddbobj, []));
                    return cmd(200, '数据删除成功');
                }
            }
        }
    }
}
