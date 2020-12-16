<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;
use Illuminate\Support\Facades\Route;
use PhpParser\Node\Stmt\TryCatch;

/**
 * @authcheckname=>节点
 */
class NodeController extends Controller
{


    private $tablename = 'sys_node';
    private $viewarray = [];

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'index'  => 'system/node_index',
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
            'classname'    => 'bail|nullable|max:128|alpha_dash',
            'functionname' => 'bail|nullable|max:256|alpha_dash',
            'routepath'    => 'bail|nullable|max:256|alpha_dash',
            'nodename'     => 'bail|nullable|max:128',
            'page'         => 'bail|nullable|integer',
            'limits'       => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table($this->tablename);
        if ('' != ($reqarr['classname'] ?? '')) {
            $DB->where('classname', 'like', '%' . ($reqarr['classname'] ?? '') . '%');
        }
        if ('' != ($reqarr['functionname'] ?? '')) {
            $DB->where('functionname', 'like', '%' . ($reqarr['functionname'] ?? '') . '%');
        }
        if ('' != ($reqarr['nodename'] ?? '')) {
            $DB->where('nodename', 'like', '%' . ($reqarr['nodename'] ?? '') . '%');
        }
        if ('' != ($reqarr['routepath'] ?? '')) {
            $DB->where('routepath', 'like', '%' . ($reqarr['routepath'] ?? '') . '%');
        }

        $dbcount = $DB->count();
        $dbobj   = $DB->orderBy('classname')->paginate($reqarr['limit'] ?? 20)->items();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
            $hasrolearr = [];
            $roleobj = DB::table('sys_role')->select('id', 'nodeidstr', 'rolename')->get();
            if (!empty($roleobj)) {
                foreach ($roleobj as $valr) {
                    $tmpnarr = comm_strintoarr($valr->nodeidstr);
                    foreach ($tmpnarr as $valtn) {
                        if (!isset($hasrolearr[$valtn][($valr->rolename ?? '')])) {
                            $tmprolename = ($valr->rolename ?? '') . '[' . ($valr->id ?? '') . ']';
                            $hasrolearr[$valtn][$tmprolename] = true;
                        }
                    }
                }
            }

            foreach ($dbobj as $valb) {
                $valb->type_show   = ('1' == $valb->type) ? comm_spanstr('前端页面', 'blue') : comm_spanstr('后端接口', 'orange');
                $valb->rolenamestr = isset($hasrolearr[($valb->id) ?? '']) ? implode(', ', array_keys($hasrolearr[($valb->id) ?? ''])) : '';
            }

            return ['code' => 0, 'msg' => '查询成功', 'count' => $dbcount, 'data' => $dbobj];
        }
    }



    /**
     * @authcheckname=>数据更新-执行
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
            'update' => 'bail|required',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }

        $routelistarr = [];
        $allRoute = Route::getRoutes();
        foreach ($allRoute as $valrou) {
            $tmpuri = $valrou->uri ?? '';
            $tmpcontroller = $valrou->action['controller'] ?? '';
            $cutarr = explode('@', $tmpcontroller, 2);
            if (count($cutarr) >= 2) {
                // $tmp_class = comm_getclassname($cutarr[0]??'');
                $tmp_class = $cutarr[0];
                $tmp_function = $cutarr[1];
                if ('' != $tmp_class && '' != $tmp_function && '' != $tmpuri) {
                    if (!isset($routelistarr[$tmp_class][$tmp_function])) {
                        $routelistarr[$tmp_class][$tmp_function] = $tmpuri;
                    }
                }
            }
        }
        foreach ($routelistarr as $keyc => $valc) {
            try {
                //获取类的注释信息
                $reflectionClass  = new \ReflectionClass($keyc);
                $classdocarr      = comm_noteanalysis($reflectionClass->getDocComment());
            } catch (\Throwable $th) {
                continue;
            }
            $com_class_name   = comm_getclassname($keyc);
            $com_nodebasename = $classdocarr['authcheckname'] ?? '';
            foreach ($routelistarr[$keyc] as $keyf => $valu) {
                try {
                    //获取方法的注释信息
                    $ReflectionMethod  = new \ReflectionMethod($keyc, $keyf);
                    $funcdocarr        = comm_noteanalysis($ReflectionMethod->getDocComment());
                } catch (\Throwable $th) {
                    continue;
                }
                $checkadd          = isset($funcdocarr['authcheckname']) ? true : false;
                $com_function_name = $keyf;
                $com_routepath     = $valu;
                $com_nodename      = $com_nodebasename . ($funcdocarr['authcheckname'] ?? '');
                $com_type          = $funcdocarr['authcheckshow'] ?? '2';
                if (true === $checkadd) {
                    $olddbobj = DB::table($this->tablename)->where('classname', $com_class_name)->where('functionname', $com_function_name)->first();
                    if (empty($olddbobj)) {
                        $dbarr                    = [];
                        $dbarr['type']            = $com_type;
                        $dbarr['classname']       = $com_class_name;
                        $dbarr['functionname']    = $com_function_name;
                        $dbarr['routepath']       = $com_routepath;
                        $dbarr['nodename']        = $com_nodename;
                        $dbarr['create_datetime'] = date('Y-m-d H:i:s');
                        $resinid = DB::table($this->tablename)->insertGetId($dbarr);
                        if ($resinid) {
                            ZzAuth::log_cudn('c', __CLASS__, __FUNCTION__, $this->tablename, $resinid, ZzAuth::data_tojstr($dbarr, []));  //记录日志
                        }
                    } else {
                        $dbarr                    = [];
                        $dbarr['type']            = $com_type;
                        $dbarr['routepath']       = $com_routepath;
                        $dbarr['nodename']        = $com_nodename;
                        $dbarr['update_datetime'] = date('Y-m-d H:i:s');
                        $resupd = DB::table($this->tablename)->where('classname', $com_class_name)->where('functionname', $com_function_name)->update($dbarr);
                        if ($resupd) {
                            ZzAuth::log_cudn('u', __CLASS__, __FUNCTION__, $this->tablename, ($olddbobj->id), ZzAuth::data_diff($olddbobj, $dbarr, []));  //记录日志
                        }
                    }
                }
            }
        }
        return cmd(200, '节点数据更新成功');
    }
}
