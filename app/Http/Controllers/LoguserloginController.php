<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;

/**
 * @authcheckname=>登陆日志
 */
class LoguserloginController extends Controller
{
    private $tablename = 'log_userlogin';
    private $viewarray = [];
    private $startdatetime_default = '';
    private $enddatetime_default = '';

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'index'  => 'system/loguserlogin_index',
            'add'    => 'system/loguserlogin_add',
            'update' => 'system/loguserlogin_update',
            'delete' => 'system/loguserlogin_delete',
        ];
        $this->startdatetime_default = date('Y-m-d 00:00:00',(time()-6*86400));
        $this->enddatetime_default   = date('Y-m-d 23:59:59');
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
        if(''==($reqarr['startdatetime']??'')) {
            $reqarr['startdatetime'] = $this->startdatetime_default;
        }
        if(''==($reqarr['enddatetime']??'')) {
            $reqarr['enddatetime'] = $this->enddatetime_default;
        }

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
        if(''==($reqarr['startdatetime']??'')) {
            $reqarr['startdatetime'] = $this->startdatetime_default;
        }
        if(''==($reqarr['enddatetime']??'')) {
            $reqarr['enddatetime'] = $this->enddatetime_default;
        }
        $validator = Validator::make($reqarr, [
            'startdatetime' => 'bail|required|date',
            'enddatetime'   => 'bail|required|date',
            'loginname'     => 'bail|nullable|between:1,64',
            'loginip'       => 'bail|nullable|between:1,64',
            'flag'          => 'bail|nullable|integer',
            'page'          => 'bail|nullable|integer',
            'limits'        => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table($this->tablename);
        if ('' != ($reqarr['startdatetime'] ?? '')) {
            $DB->where('create_datetime', '>=', ($reqarr['startdatetime'] ?? ''));
        }
        if ('' != ($reqarr['enddatetime'] ?? '')) {
            $DB->where('create_datetime', '<=', ($reqarr['enddatetime'] ?? ''));
        }
        if ('' != ($reqarr['flag'] ?? '')) {
            $DB->where('flag', ($reqarr['flag'] ?? '0'));
        }
        if ('' != ($reqarr['loginname'] ?? '')) {
            $DB->where('loginname', 'like', '%' . ($reqarr['loginname'] ?? '') . '%');
        }
        if ('' != ($reqarr['loginip'] ?? '')) {
            $DB->where('loginip', 'like', '%' . ($reqarr['loginip'] ?? '') . '%');
        }

        $dbcount = $DB->count();
        $dbobj   = $DB->orderBy('id','desc')->paginate($reqarr['limit'] ?? 20)->items();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
            
            foreach ($dbobj as $valb) {
                $valb->flag_show = ('1' == $valb->flag) ? comm_spanstr('登陆成功', 'green') : comm_spanstr('登陆失败', 'red');
            }

            return ['code' => 0, 'msg' => '查询成功', 'count' => $dbcount, 'data' => $dbobj];
        }
    }
}
