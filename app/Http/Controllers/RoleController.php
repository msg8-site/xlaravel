<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;

/**
 * @authcheckname=>角色
 */
class RoleController extends Controller
{
    private $tablename = 'sys_role';
    private $viewarray = [];

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'index'  => 'system/role_index',
            'add'    => 'system/role_add',
            'update' => 'system/role_update',
            'delete' => 'system/role_delete',
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
            'rolename' => 'bail|nullable|between:1,64',
            'page'     => 'bail|nullable|integer',
            'limits'   => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table($this->tablename);
        if ('' != ($reqarr['rolename'] ?? '')) {
            $DB->where('rolename', 'like', '%' . ($reqarr['rolename'] ?? '') . '%');
        }

        $dbcount = $DB->count();
        $dbobj   = $DB->paginate($reqarr['limit'] ?? 20)->items();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
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
        $menulist = [];
        $nodelist = [];

        $dbobj   = DB::table('sys_menu')->orderBy('fid', 'asc')->orderBy('orderbyid', 'desc')->get();
        if (!empty($dbobj)) {
            foreach ($dbobj as $valm) {
                $menu_id = $valm->id ?? 0;
                $menu_fid = $valm->fid ?? null;
                if (null !== $menu_fid && $menu_id > 0) {
                    if (0 == $menu_fid) {
                        //根节点
                        if (!isset($menulist[$menu_id])) {
                            $menulist[$menu_id]['m'] = get_object_vars($valm);
                        }
                    } else {
                        if (isset($menulist[$menu_fid])) {
                            $menulist[$menu_fid]['c'][] = get_object_vars($valm);
                        }
                    }
                }
            }
        }

        $dbobj   = DB::table('sys_node')->orderBy('classname', 'asc')->get();
        if (!empty($dbobj)) {
            $nodelist = [];
            $cutflag = false;
            foreach ($dbobj as $vald) {
                if ('IndexController' == ($vald->classname ?? '')) {
                    $vald->tsmsg = '建议勾选';
                }
                if (false === $cutflag) {
                    $cutflag = $vald->classname;
                }
                if ($cutflag == $vald->classname) {
                    array_push($nodelist, $vald);
                } else {
                    array_push($nodelist, 'jump');
                    array_push($nodelist, $vald);
                }
                $cutflag = $vald->classname;
            }
        }


        $resarr['menulist'] = $menulist;
        $resarr['nodelist'] = $nodelist;

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
            'selectnode' => 'bail|nullable|array',
            'selectmenu' => 'bail|nullable|array',
            'rolename'   => 'bail|required|between:1,64',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $dbobj = DB::table($this->tablename)->where('rolename', ($reqarr['rolename'] ?? ''))->first();
        if (!empty($olddbobj)) {
            return cmd(400, '【错误】角色名已经存在，不可重复添加');
        } else {
            $dbarr = [];
            $dbarr['rolename']        = $reqarr['rolename'] ?? '';
            $dbarr['nodeidstr']       = (is_array($reqarr['selectnode'] ?? '') && count($reqarr['selectnode'] ?? '') > 0) ? implode(',', $reqarr['selectnode'] ?? []) : '';
            $dbarr['menuidstr']       = (is_array($reqarr['selectmenu'] ?? '') && count($reqarr['selectmenu'] ?? '') > 0) ? implode(',', $reqarr['selectmenu'] ?? []) : '';
            $dbarr['backup1']         = $reqarr['backup1'] ?? '';
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
            $olddbobj = $DB->where('id', ($reqarr['id'] ?? '0'))->first();
            if (empty($olddbobj)) {
                $resarr['errmsg'] = '【错误】未找到对应数据';
            } else {
                $hasmenuarr = comm_strintoarr($olddbobj->menuidstr ?? '');
                $hasnodearr = comm_strintoarr($olddbobj->nodeidstr ?? '');
                $dbobj   = DB::table('sys_menu')->orderBy('fid', 'asc')->orderBy('orderbyid', 'desc')->get();
                if (!empty($dbobj)) {
                    foreach ($dbobj as $valm) {
                        if (in_array($valm->id, $hasmenuarr)) {
                            $valm->check = 'checked';
                        }
                        $menu_id = $valm->id ?? 0;
                        $menu_fid = $valm->fid ?? null;
                        if (null !== $menu_fid && $menu_id > 0) {
                            if (0 == $menu_fid) {
                                //根节点
                                if (!isset($menulist[$menu_id])) {
                                    $menulist[$menu_id]['m'] = get_object_vars($valm);
                                }
                            } else {
                                if (isset($menulist[$menu_fid])) {
                                    $menulist[$menu_fid]['c'][] = get_object_vars($valm);
                                }
                            }
                        }
                    }
                }

                $dbobj   = DB::table('sys_node')->orderBy('classname', 'asc')->get();
                if (!empty($dbobj)) {
                    $nodelist = [];
                    $cutflag = false;
                    foreach ($dbobj as $vald) {
                        if ('IndexController' == ($vald->classname ?? '')) {
                            $vald->tsmsg = '建议勾选';
                        }
                        if (in_array($vald->id, $hasnodearr)) {
                            $vald->check = 'checked';
                        }
                        if (false === $cutflag) {
                            $cutflag = $vald->classname;
                        }
                        if ($cutflag == $vald->classname) {
                            array_push($nodelist, $vald);
                        } else {
                            array_push($nodelist, 'jump');
                            array_push($nodelist, $vald);
                        }
                        $cutflag = $vald->classname;
                    }
                }

                $resarr['menulist'] = $menulist;
                $resarr['nodelist'] = $nodelist;
                $resarr['data'] = $olddbobj;
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
            'uplockid'   => 'bail|required|numeric',
            'id'         => 'bail|required|integer',
            'selectnode' => 'bail|nullable|array',
            'selectmenu' => 'bail|nullable|array',
            'rolename'   => 'bail|required|between:1,64',
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
            $dbarr['rolename']        = $reqarr['rolename'] ?? '';
            $dbarr['nodeidstr']       = (is_array($reqarr['selectnode'] ?? '') && count($reqarr['selectnode'] ?? '') > 0) ? implode(',', $reqarr['selectnode'] ?? []) : '';
            $dbarr['menuidstr']       = (is_array($reqarr['selectmenu'] ?? '') && count($reqarr['selectmenu'] ?? '') > 0) ? implode(',', $reqarr['selectmenu'] ?? []) : '';
            $dbarr['backup1']         = $reqarr['backup1'] ?? '';
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
