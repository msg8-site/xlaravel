<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;

/**
 * @authcheckname=>参考样例
 */
class ExampleController extends Controller
{
    private $tablename = 'tpl_example';
    private $viewarray = [];

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'index'  => 'example/page_index',
            'add'    => 'example/page_add',
            'update' => 'example/page_update',
            'delete' => 'example/page_delete',
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
            'name'   => 'bail|nullable|max:64',
            'status' => 'bail|nullable|integer',
            'status' => 'bail|nullable|integer',
            'page'   => 'bail|nullable|integer',
            'limits' => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table($this->tablename);
        if ('' == ($reqarr['status'] ?? '')) {
            $DB->whereIn('status', [1, 2]);
        } else if (is_numeric($reqarr['status'] ?? '')) {
            $DB->where('status', ($reqarr['status'] ?? '0'));
        }
        if ('' != ($reqarr['name'] ?? '')) {
            $DB->where('name', 'like', '%' . ($reqarr['name'] ?? '') . '%');
        }

        $dbcount = $DB->count();
        $dbobj   = $DB->orderBy('id', 'desc')->paginate($reqarr['limit'] ?? 20)->items();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
            foreach ($dbobj as $valb) {
                $valb->status_show    = comm_colorspan($valb->status);
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

        $resarr['option_status'] = comm_ccoption('', 'typestatus', false);

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
            'name'           => 'bail|required|max:64',
            'status'         => 'bail|required|integer',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $dbobj = DB::table($this->tablename)->where('name', ($reqarr['name'] ?? ''))->first();
        if (!empty($olddbobj)) {
            return cmd(400, '【错误】唯一标识名称已经存在，不可重复添加');
        } else {
            $dbarr = [];
            $dbarr['name']            = $reqarr['name'] ?? '';
            $dbarr['status']          = $reqarr['status'] ?? '0';
            $dbarr['testdata1']       = $reqarr['testdata1'] ?? '';
            $dbarr['testdata2']       = $reqarr['testdata2'] ?? '';
            $dbarr['testdata3']       = $reqarr['testdata3'] ?? '';
            $dbarr['testdata4']       = $reqarr['testdata4'] ?? '';
            $dbarr['testdata5']       = $reqarr['testdata5'] ?? '';
            $dbarr['backup1']         = $reqarr['backup1'] ?? '';
            $dbarr['backup2']         = $reqarr['backup2'] ?? '';
            $dbarr['backup3']         = $reqarr['backup3'] ?? '';
            $dbarr['create_datetime'] = date('Y-m-d H:i:s');

            $resinid = DB::table($this->tablename)->insertGetId($dbarr);
            if (!$resinid) {
                return cmd(400, '【错误】数据添加失败，系统错误');
            } else {
                ZzAuth::log_cudn('c', __CLASS__, __FUNCTION__, $this->tablename, $resinid, ZzAuth::data_tojstr($dbarr, []));  //记录日志
                return cmd(200, '数据添加成功');
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
            $DB = DB::table($this->tablename);
            $dbobj = $DB->where('id', ($reqarr['id'] ?? '0'))->first();
            if (empty($dbobj)) {
                $resarr['errmsg'] = '【错误】未找到对应数据';
            } else {
                $resarr['data']                  = get_object_vars($dbobj);
                $resarr['data']['option_status'] = comm_ccoption($resarr['data']['status'], 'typestatus', false);
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
            'uplockid' => 'bail|required|numeric',
            'id'       => 'bail|required|integer',
            'status'   => 'bail|required|integer',
            'name'     => 'bail|required|max:64',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $olddbobj = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? 0))->first();
        if (empty($olddbobj)) {
            return cmd(400, '【错误】数据不存在，无法修改');
        } else if (($reqarr['uplockid'] ?? '') != ($olddbobj->uplockid ?? '')) {
            return cmd(400, '【错误】数据发生改动，请刷新数据修改页面，获取最新数据后重新执行修改操作');
        } else {
            $errmsg = '';
            $dbarr = [];
            $dbarr['uplockid']        = date('ymdHis') . mt_rand(100000, 999999);
            $dbarr['status']          = $reqarr['status'] ?? '0';
            $dbarr['name']            = $reqarr['name'] ?? '';
            $dbarr['testdata1']       = $reqarr['testdata1'] ?? '';
            $dbarr['testdata2']       = $reqarr['testdata2'] ?? '';
            $dbarr['testdata3']       = $reqarr['testdata3'] ?? '';
            $dbarr['testdata4']       = $reqarr['testdata4'] ?? '';
            $dbarr['testdata5']       = $reqarr['testdata5'] ?? '';
            $dbarr['backup1']         = $reqarr['backup1'] ?? '';
            $dbarr['backup2']         = $reqarr['backup2'] ?? '';
            $dbarr['backup3']         = $reqarr['backup3'] ?? '';
            $dbarr['update_datetime'] = date('Y-m-d H:i:s');
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
