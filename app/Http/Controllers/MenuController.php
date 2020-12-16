<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;


/**
 * @authcheckname=>菜单
 */
class MenuController extends Controller
{
    private $tablename = 'sys_menu';
    private $viewarray = [];

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'index'  => 'system/menu_index',
            'add'    => 'system/menu_add',
            'update' => 'system/menu_update',
            'delete' => 'system/menu_delete',
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
            'menuname' => 'bail|nullable|max:64',
            'menupath' => 'bail|nullable|max:256',
            'page'     => 'bail|nullable|integer',
            'limits'   => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table($this->tablename);
        if ('' != ($reqarr['menuname'] ?? '')) {
            $DB->where('menuname', 'like', '%' . ($reqarr['menuname'] ?? '') . '%');
        }
        if ('' != ($reqarr['menupath'] ?? '')) {
            $DB->where('menupath', 'like', '%' . ($reqarr['menupath'] ?? '') . '%');
        }

        $dbcount = $DB->count();
        $dbobj   = $DB->orderBy('fid', 'asc')->orderBy('orderbyid', 'desc')->get();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
            $hasrolearr = [];
            $roleobj = DB::table('sys_role')->select('id', 'menuidstr', 'rolename')->get();
            if (!empty($roleobj)) {
                foreach ($roleobj as $valr) {
                    $tmpnarr = comm_strintoarr($valr->menuidstr);
                    foreach ($tmpnarr as $valtn) {
                        if (!isset($hasrolearr[$valtn][($valr->rolename ?? '')])) {
                            $tmprolename = ($valr->rolename ?? '') . '[' . ($valr->id ?? '') . ']';
                            $hasrolearr[$valtn][$tmprolename] = true;
                        }
                    }
                }
            }

            $menulist = [];
            $menuarr = [];
            foreach ($dbobj as $valm) {
                $menu_id = $valm->id ?? 0;
                $menu_fid = $valm->fid ?? null;
                if (null !== $menu_fid && $menu_id > 0) {
                    if (0 == $menu_fid) {
                        //根节点
                        if (!isset($menuarr[$menu_id])) {
                            $menuarr[$menu_id]['m'] = $valm;
                        }
                    } else {
                        if (isset($menuarr[$menu_fid])) {
                            $menuarr[$menu_fid]['c'][] = $valm;
                        }
                    }
                }
            }
            foreach ($menuarr as $keys => $vals) {
                if (isset($menuarr[$keys]['m'])) {
                    $tmps = $menuarr[$keys]['m'];
                    $tmps->showmenuname = comm_spanstr(($tmps->menuname ?? ''), 'blue');
                    $tmps->rolenamestr = isset($hasrolearr[($tmps->id) ?? '']) ? implode(', ', array_keys($hasrolearr[($tmps->id) ?? ''])) : '';
                    array_push($menulist, $tmps);
                }
                if (isset($menuarr[$keys]['c'])) {
                    foreach ($menuarr[$keys]['c'] as $keyss => $valss) {
                        $tmps = $menuarr[$keys]['c'][$keyss];
                        $tmps->showmenuname = comm_spanstr(('|---- ' . $tmps->menuname ?? ''), 'green');
                        $tmps->rolenamestr = isset($hasrolearr[($tmps->id) ?? '']) ? implode(', ', array_keys($hasrolearr[($tmps->id) ?? ''])) : '';
                        array_push($menulist, $tmps);
                    }
                }
            }

            return ['code' => 0, 'msg' => '查询成功', 'count' => $dbcount, 'data' => $menulist];
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

        $resarr['errmsg'] = '';  //不为空时页面直接仅显示本信息
        if (!is_numeric($reqarr['id'] ?? '')) {
            $resarr['errmsg'] = '【错误】非法操作，数据格式错误';
        } else {
            if ('0' === (string)($reqarr['id'] ?? '')) {
                //直接添加根级分类目录
                $resarr['fid'] = '0';
                $resarr['fidname'] = '没有父级菜单，本次添加为顶级菜单分类';
            } else {
                //二级子菜单
                $dbobj = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? ''))->first();
                if (empty($dbobj)) {
                    $resarr['errmsg'] = '【错误】没有找到关联id数据';
                } else if ('0' !== (string)($dbobj->fid ?? '')) {
                    $resarr['errmsg'] = '【错误】菜单仅支持两级，非菜单分类目录不可添加子菜单';
                } else {
                    $resarr['fid'] = $dbobj->id;
                    $resarr['fidname'] = $dbobj->menuname;
                }
            }
        }

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
            'fid'       => 'bail|required|integer',
            'orderbyid' => 'bail|required|integer',
            'menuname'  => 'bail|required|between:1,64',
            'menupath'  => 'bail|max:256',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        if ('0' === (string)($reqarr['fid'] ?? '')) {
            $reqarr['menupath'] = '';
        }

        $dbarr                    = [];
        $dbarr['fid']             = $reqarr['fid'] ?? '0';
        $dbarr['menuname']        = $reqarr['menuname'] ?? '';
        $dbarr['menupath']        = $reqarr['menupath'] ?? '';
        $dbarr['orderbyid']       = $reqarr['orderbyid'] ?? '';
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
                $resarr['data'] = get_object_vars($dbobj);
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
            'id'        => 'bail|required|integer',
            'orderbyid' => 'bail|required|integer',
            'menuname'  => 'bail|required|between:1,64',
            'menupath'  => 'bail|max:256',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $olddbobj = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? 0))->first();
        if (empty($olddbobj)) {
            return cmd(400, '【错误】数据不存在，无法修改');
        } else {
            if ('0' === (string)($olddbobj->fid ?? '')) {
                $reqarr['menupath'] = '';
            }

            $dbarr                    = [];
            $dbarr['menuname']        = $reqarr['menuname'] ?? '';
            $dbarr['menupath']        = $reqarr['menupath'] ?? '';
            $dbarr['orderbyid']       = $reqarr['orderbyid'] ?? '';
            $dbarr['backup1']         = $reqarr['backup1'] ?? '';
            $dbarr['update_datetime'] = date('Y-m-d H:i:s');

            $resupd = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? 0))->update($dbarr);
            if (!$resupd) {
                return cmd(400, '【错误】数据修改失败，系统错误');
            } else {
                ZzAuth::log_cudn('u', __CLASS__, __FUNCTION__, $this->tablename, ($reqarr['id'] ?? 0), ZzAuth::data_diff($olddbobj, $dbarr, []));  //记录日志
                return cmd(200, '数据修改成功');
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
            //判断该数据下是否存在数据
            $haschildobj = DB::table($this->tablename)->where('fid', $olddbobj->id ?? '0')->first();
            if (!empty($haschildobj)) {
                return cmd(400, '【错误】该数据下存在子数据，无法删除，请先删除子数据');
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
