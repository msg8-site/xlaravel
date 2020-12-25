<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;
use \Parsedown;
use Illuminate\Support\Facades\Storage;

/**
 * @authcheckname=>文档
 */
class MarkdownController extends Controller
{
    private $tablename = 'doc_markdown';
    private $viewarray = [];

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'index'       => 'markdown/page_index',
            'add'         => 'markdown/page_add',
            'update'      => 'markdown/page_update',
            'delete'      => 'markdown/page_delete',
            'docshow'     => 'markdown/page_docshow',
            'childiframe' => 'markdown/childiframe',
            'leftmenu'    => 'markdown/leftmenu',
        ];
    }


    /**
     * @authcheckname=>子框架
     * @authcheckshow=>1
     */
    public function childiframe(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['reqarr' => $reqarr, 'resarr' => $resarr]);
    }
    /**
     * @authcheckname=>子框架菜单
     * @authcheckshow=>1
     */
    public function leftmenu(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];

        $menulist = [];
        $DB = DB::table($this->tablename)->select('id', 'flag', 'docname', 'typename', 'orderbyid');
        $dbobj = $DB->orderBy('flag')->orderBy('typename', 'asc')->orderBy('orderbyid', 'desc')->get();
        if (!empty($dbobj)) {
            foreach ($dbobj as $valm) {
                $tmp_id = $valm->id ?? '';
                $tmp_docname = $valm->docname ?? '';
                $tmp_typename = $valm->typename ?? '';
                if (!isset($menulist[$tmp_typename])) {
                    $menulist[$tmp_typename] = [];
                }
                array_push($menulist[$tmp_typename], [$tmp_docname, $tmp_id, $tmp_typename]);
            }
        }

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['menulist' => $menulist]);
    }

    /**
     * @authcheckname=>子框架主页
     * @authcheckshow=>1
     */
    public function rightbody(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];
        echo '<h1 style="margin:30px;">请点击左侧菜单查看文档</h1>';
    }

    /**
     * @authcheckname=>搜索
     * @authcheckshow=>2
     */
    public function search(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];

        $menulist = [];
        $DB = DB::table($this->tablename)->select('id', 'flag', 'docname', 'typename', 'orderbyid');
        if ('' != ($reqarr['searchval'] ?? '')) {
            $DB->orWhere('docname', 'like', '%' . ($reqarr['searchval'] ?? '') . '%');
        }
        if ('' != ($reqarr['searchval'] ?? '')) {
            $DB->orWhere('typename', 'like', '%' . ($reqarr['searchval'] ?? '') . '%');
        }
        if ('' != ($reqarr['searchval'] ?? '')) {
            $DB->orWhere('content', 'like', '%' . ($reqarr['searchval'] ?? '') . '%');
        }
        $dbobj = $DB->orderBy('flag')->orderBy('typename', 'asc')->orderBy('orderbyid', 'desc')->get();
        if (!empty($dbobj)) {
            foreach ($dbobj as $valm) {
                $tmp_id = $valm->id ?? '';
                $tmp_docname = $valm->docname ?? '';
                $tmp_typename = $valm->typename ?? '';
                if (!isset($menulist[$tmp_typename])) {
                    $menulist[$tmp_typename] = [];
                }
                array_push($menulist[$tmp_typename], [$tmp_docname, $tmp_id, $tmp_typename]);
            }
        }
        $showhtml = '';
        foreach ($menulist as $keym => $valm) {
            $showhtml .= '<li class="layui-nav-item">';
            $showhtml .= '<a class="mainmenuname" href="javascript:;">' . $keym . '</a>';
            if (count($menulist[$keym]) > 0) {
                $showhtml .= '<dl class="layui-nav-child">';
                foreach ($menulist[$keym] as $keymc => $valmc) {
                    $showhtml .= '<dd><a class="childmenuname" href="markdown_docshow?id=' . $valmc[1] . '" target="iframeright">' . $valmc[0] . '</a></dd>';
                }
                $showhtml .= '</dl>';
            }
            $showhtml .= '</li>';
        }
        return cmd(200, '数据添加成功', $showhtml);
    }

    /**
     * @authcheckname=>页面查看
     * @authcheckshow=>1
     */
    public function docshow(Request $request)
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
                $Parsedown = new Parsedown();
                $resarr['markdown'] = $Parsedown->text($dbobj->content ?? '');
                $resarr['data']     = get_object_vars($dbobj);
            }
        }

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['reqarr' => $reqarr, 'resarr' => $resarr]);
    }
    /**
     * @authcheckname=>实时预览
     * @authcheckshow=>2
     */
    public function realtimeshow(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];

        if ('' == ($reqarr['contentval'] ?? '')) {
            return cmd(200, '数据为空', '');
        } else {
            $Parsedown = new Parsedown();
            $resdata = $Parsedown->text(($reqarr['contentval'] ?? ''));

            return cmd(200, '数据生成成功', $resdata);
        }
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
            'id'      => 'bail|nullable|integer',
            'flag'    => 'bail|nullable|integer',
            'docname' => 'bail|nullable|max:64',
            'content' => 'bail|nullable|max:64',
            'page'    => 'bail|nullable|integer',
            'limits'  => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table($this->tablename)->select('id', 'flag', 'docname', 'typename', 'orderbyid', 'create_datetime', 'update_datetime');
        if ('' != ($reqarr['id'] ?? '')) {
            $DB->where('id', ($reqarr['id'] ?? '0'));
        }
        if ('' != ($reqarr['flag'] ?? '')) {
            $DB->where('flag', ($reqarr['flag'] ?? '0'));
        }
        if ('' != ($reqarr['docname'] ?? '')) {
            $DB->where('docname', 'like', '%' . ($reqarr['docname'] ?? '') . '%');
        }
        if ('' != ($reqarr['content'] ?? '')) {
            $DB->where('content', 'like', '%' . ($reqarr['content'] ?? '') . '%');
        }

        $dbcount = $DB->count();
        $dbobj   = $DB->orderBy('flag')->orderBy('typename')->orderBy('orderbyid', 'desc')->paginate($reqarr['limit'] ?? 20)->items();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
            foreach ($dbobj as $valb) {
                $valb->flag_show = ('1' == $valb->flag) ? comm_spanstr('开放', 'green') : comm_spanstr('封闭', 'blue');
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

        $dbobj = DB::table($this->tablename)->select('typename')->groupBy('typename')->orderBy('typename')->get();
        if (!empty($dbobj)) {
            $tmparr = [];
            foreach ($dbobj as $vald) {
                $tmparr[($vald->typename ?? '')] = ($vald->typename ?? '');
            }
            $resarr['option_typename'] = comm_ccoption('', $tmparr, false);
        }

        $resarr['option_flag'] = comm_ccoption('2', 'openclose', false);

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
            'flag'      => 'bail|required|integer',
            'docname'   => 'bail|required|max:64',
            'typename'  => 'bail|required|max:64',
            'orderbyid' => 'bail|required|integer',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $dbobj = DB::table($this->tablename)->where('docname', ($reqarr['docname'] ?? ''))->first();
        if (!empty($dbobj)) {
            return cmd(400, '【错误】文档名称已经存在，不可重复添加');
        } else {
            $dbarr = [];
            $dbarr['flag']            = $reqarr['flag'] ?? '2';
            $dbarr['docname']         = $reqarr['docname'] ?? '';
            $dbarr['typename']        = $reqarr['typename'] ?? '';
            $dbarr['orderbyid']       = $reqarr['orderbyid'] ?? '100';
            $dbarr['content']         = $reqarr['content'] ?? '';
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
                $resarr['data'] = get_object_vars($dbobj);
                $resarr['data']['option_flag'] = comm_ccoption($resarr['data']['flag'], 'openclose', false);
            }

            $dbobj = DB::table($this->tablename)->select('typename')->groupBy('typename')->orderBy('typename')->get();
            if (!empty($dbobj)) {
                $tmparr = [];
                foreach ($dbobj as $vald) {
                    $tmparr[($vald->typename ?? '')] = ($vald->typename ?? '');
                }
                $resarr['option_typename'] = comm_ccoption('', $tmparr, false);
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
            'uplockid'  => 'bail|required|numeric',
            'id'        => 'bail|required|integer',
            'flag'      => 'bail|required|integer',
            'docname'   => 'bail|required|max:64',
            'typename'  => 'bail|required|max:64',
            'orderbyid' => 'bail|required|integer',
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
            $dbarr['flag']            = $reqarr['flag'] ?? '2';
            $dbarr['docname']         = $reqarr['docname'] ?? '';
            $dbarr['typename']        = $reqarr['typename'] ?? '';
            $dbarr['orderbyid']       = $reqarr['orderbyid'] ?? '100';
            $dbarr['content']         = $reqarr['content'] ?? '';
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

    /**
     * @authcheckname=>备份-执行
     * @authcheckshow=>2
     */
    public function backup_exec(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];
        if ('yes' != ($reqarr['backup'] ?? '')) {
            return cmd(400, '【错误】非法操作，备份校验参数不正确');
        }

        $count_sum = 0;
        $count_suc = 0;
        $count_err = 0;
        $DB = DB::table($this->tablename);
        $DB->orderBy('id')->chunk(50, function ($dbobj) use (&$count_sum, &$count_suc, &$count_err) {
            foreach ($dbobj as $vald) {
                ++$count_sum;
                $backuppath = 'markdownback_' . date('Ymd_His') . '/' . ($vald->typename ?? '') . '_' . ($vald->docname ?? '') . '.md';
                $backupdata = $vald->content ?? '';
                $r = Storage::put($backuppath, $backupdata);
                if ($r) {
                    ++$count_suc;
                } else {
                    ++$count_err;
                }
            }
        });
        return cmd(200, '数据备份执行完成--总数：' . $count_sum . '，成功：' . $count_suc . '，失败：' . $count_err);
    }

    /**
     * @authcheckname=>备份恢复-执行
     * @authcheckshow=>2
     */
    public function recovery_exec(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];
        if ('' == ($reqarr['recoverydirname'] ?? '')) {
            return cmd(400, '【错误】恢复对应文件夹名称不能为空');
        }
        if (true !== Storage::exists(($reqarr['recoverydirname'] ?? ''))) {
            return cmd(400, '【错误】恢复对应文件夹名称不存在');
        } else {
            $filesarr = Storage::files(($reqarr['recoverydirname'] ?? ''));
            if (!is_array($filesarr) || count($filesarr) <= 0) {
                return cmd(400, '【错误】恢复对应文件夹下没有对应恢复文件');
            } else {
                $count_sum = 0;
                $count_suc = 0;
                $count_err = 0;
                $count_has = 0;
                foreach ($filesarr as $valf) {
                    ++$count_sum;
                    $tmp_filename = rtrim(basename($valf), '.md');
                    $tmparr = explode('_', $tmp_filename, 2);
                    $tmp_typename = $tmparr[0] ?? '';
                    $tmp_docname = $tmparr[1] ?? '';
                    $tmp_content = Storage::get($valf);

                    $dbobj = DB::table($this->tablename)->where('docname', $tmp_docname)->first();
                    if (!empty($dbobj)) {
                        ++$count_has;
                    } else {
                        $dbarr = [];
                        $dbarr['flag']            = '2';
                        $dbarr['docname']         = $tmp_docname;
                        $dbarr['typename']        = $tmp_typename;
                        $dbarr['orderbyid']       = '100';
                        $dbarr['content']         = $tmp_content;
                        $dbarr['create_datetime'] = date('Y-m-d H:i:s');

                        $resinid = DB::table($this->tablename)->insertGetId($dbarr);
                        if (!$resinid) {
                            ++$count_err;
                            return cmd(400, '【错误】数据添加失败，系统错误');
                        } else {
                            ++$count_suc;
                            ZzAuth::log_cudn('c', __CLASS__, __FUNCTION__, $this->tablename, $resinid, ZzAuth::data_tojstr($dbarr, []));  //记录日志
                        }
                    }
                }
                return cmd(200, '数据恢复执行完成--总数：' . $count_sum . '，成功：' . $count_suc . '，失败：' . $count_err . '，跳过：' . $count_has);
            }
        }
    }
}
