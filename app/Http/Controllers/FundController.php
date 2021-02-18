<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libraries\ZzAuth;
use App\Rules\zd_alpha;
use App\Rules\zd_alnum;

/**
 * @authcheckname=>基金辅助工具
 */
class FundController extends Controller
{
    private $tablename = 'x_fundmain';
    private $viewarray = [];

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'index'        => 'fund/page_index',
            'add'          => 'fund/page_add',
            'update'       => 'fund/page_update',
            'delete'       => 'fund/page_delete',
            'index_update' => 'fund/page_index_update',
            'childupdate'  => 'fund/page_childupdate',
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
            'fundcode' => 'bail|nullable|max:16',
            'page'     => 'bail|nullable|integer',
            'limits'   => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table($this->tablename);
        $DB->where('create_username', session(SESS_USERNAME, ''));
        if ('' != ($reqarr['fundcode'] ?? '')) {
            $DB->where('fundcode', 'like', '%' . ($reqarr['fundcode'] ?? '') . '%');
        }

        $dbcount = $DB->count();
        $dbobj   = $DB->orderBy('orderbyid', 'desc')->orderBy('id', 'desc')->paginate($reqarr['limit'] ?? 20)->items();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
            foreach ($dbobj as $valb) {
                $valb->idfundcodename = ($valb->id ?? '') . '<br>' . ($valb->fundcode ?? '') . '<br>' . ($valb->fundname ?? '');
                $valb->sumhas_countmoneyaverageprofit = '累计持有:<br>' . ($valb->sumhas_count ?? '') . '份<br>' . ($valb->sumhas_money ?? '') . '元<br>' . ($valb->sumhas_average ?? '') . '<br>累计盈利:<br>' . ($valb->sumhas_profit ?? '') . '元';
                $valb->sumhas_suminout = '累计买入:<br>' . ($valb->sumhas_incount ?? '0') . '份<br>' . ($valb->sumhas_inmoney ?? '0') . '元<br>累计卖出:<br>' . ($valb->sumhas_outcount ?? '0') . '份<br>' . ($valb->sumhas_outmoney ?? '0') . '元';
                $tmparr = json_decode(($valb->jingzhi_zhangdie_jsonstring ?? ''), true);
                $valb->jingzhi_zhangdie_jsonstring = '';
                for ($i = 0; $i <= 200; $i++) {
                    if (in_array($i, [0, 1, 2, 3, 4, 5, 6, 10, 20, 40, 60, 80, 100, 120])) {
                        $tmpname = 'zhangdiejingzhi' . $i;
                        $valb->$tmpname = substr(($tmparr[$i]['thedate'] ?? '00-00'), -8) . '<br>' .
                            ($tmparr[$i]['jingzhi'] ?? '0') . '<br>' .
                            comm_fundcolor(($tmparr[$i]['zhangdie_day'] ?? '0') . '%') . '<br>' .
                            '<span style="color:#333;text-decoration: underline #333;">' . ($tmparr[$i]['zhangdie_sum'] ?? '0') . '%</span><br>' .
                            ($tmparr[$i]['sumcount'] ?? '0') . '份<br>' .
                            ($tmparr[$i]['summoney'] ?? '0') . '元<br>' .
                            ($tmparr[$i]['sumprofit'] ?? '0') . '元<br>';
                    }
                }
                $valb->mairugusuan = '1k：' . round(($valb->new_jingzhi * 1000), 2) . '<br>' .
                    '2k：' . round(($valb->new_jingzhi * 2000), 2) . '<br>' .
                    '3k：' . round(($valb->new_jingzhi * 3000), 2) . '<br>' .
                    '4k：' . round(($valb->new_jingzhi * 4000), 2) . '<br>' .
                    '5k：' . round(($valb->new_jingzhi * 5000), 2) . '<br>' .
                    '6k：' . round(($valb->new_jingzhi * 6000), 2) . '<br>' .
                    '7k：' . round(($valb->new_jingzhi * 7000), 2) . '<br>';
            }

            return ['code' => 0, 'msg' => '查询成功', 'count' => $dbcount, 'data' => $dbobj];
        }
    }

    /**
     * @authcheckname=>子表数据查看
     * @authcheckshow=>1
     */
    public function index_update(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return $resmsg;
        }
        $reqarr = $request->all();
        $resarr = [];

        $maindbobj = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? '0'))->where('create_username', session(SESS_USERNAME, ''))->select('id', 'fundcode', 'fundname', 'new_shijian', 'new_jingzhi', 'sumhas_count', 'sumhas_money', 'sumhas_average', 'sumhas_profit')->first();
        if (!empty($maindbobj)) {
            $resarr['maindata'] = get_object_vars($maindbobj);
        }

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['reqarr' => $reqarr, 'resarr' => $resarr]);
    }

    /**
     * @authcheckname=>子表数据查看-返回
     * @authcheckshow=>2
     */
    public function index_update_tabledata(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return ['code' => 9, 'msg' => $resmsg, 'data' => []];
        }
        $reqarr = $request->all();
        $resarr = [];
        $validator = Validator::make($reqarr, [
            'fid'    => 'bail|required|integer',
            'page'   => 'bail|nullable|integer',
            'limits' => 'bail|nullable|integer',
        ]);
        if ($validator->fails()) {
            return ['code' => 9, 'msg' =>  '【错误】' . $validator->errors()->all()[0], 'data' => []];
        }

        $DB = DB::table('x_fundchild');
        $DB->where('fid', ($reqarr['fid'] ?? '0'));

        $dbcount = $DB->count();
        $dbobj   = $DB->orderBy('id', 'desc')->paginate($reqarr['limit'] ?? 20)->items();
        if (empty($dbobj)) {
            return ['code' => 9, 'msg' => '【错误】数据不存在', 'data' => []];
        } else {
            $weekarray = array("日", "一", "二", "三", "四", "五", "六");
            foreach ($dbobj as $valb) {
                $tmpweek = date('w', strtotime($valb->thedate));
                $valb->theweek = '周' . $weekarray[$tmpweek];

                $valb->jingzhi  = round($valb->jingzhi, 4);
                $valb->inmoney  = round($valb->inmoney, 2);
                $valb->incount  = (0 == $valb->jingzhi) ? 0 : round(($valb->inmoney / $valb->jingzhi), 2);
                $valb->outcount = round($valb->outcount, 2);
                $valb->outmoney = round(($valb->jingzhi * $valb->outcount), 2);
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
            'fundcode'       => ['bail', 'required', 'between:6,6', (new zd_alnum)],
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $dbobj = DB::table($this->tablename)->where('fundcode', ($reqarr['fundcode'] ?? ''))->first();
        if (!empty($olddbobj)) {
            return cmd(400, '【错误】唯一标识名称已经存在，不可重复添加');
        } else {
            $dbarr = [];
            $dbarr['fundcode']        = $reqarr['fundcode'] ?? '';
            $dbarr['create_datetime'] = date('Y-m-d H:i:s');
            $dbarr['create_username'] = session(SESS_USERNAME, '');

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
     * @authcheckname=>子表数据修改
     * @authcheckshow=>1
     */
    public function childupdate(Request $request)
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
            $DB = DB::table('x_fundchild');
            $dbobj = $DB->where('id', ($reqarr['id'] ?? '0'))->first();
            if (empty($dbobj)) {
                $resarr['errmsg'] = '【错误】未找到对应数据';
            } else {
                $weekarray = array("日", "一", "二", "三", "四", "五", "六");
                $maindbobj = DB::table($this->tablename)->where('id', ($dbobj->fid ?? '0'))->select('id', 'fundcode', 'fundname', 'new_shijian', 'new_jingzhi', 'sumhas_count', 'sumhas_money', 'sumhas_average', 'sumhas_profit')->first();
                $resarr['maindata'] = get_object_vars($maindbobj);
                $resarr['data']     = get_object_vars($dbobj);
                $resarr['data']['theweek'] = $weekarray[date('w', strtotime($resarr['data']['thedate'] ?? ''))] ?? '';
            }
        }

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['reqarr' => $reqarr, 'resarr' => $resarr]);
    }

    /**
     * @authcheckname=>子表数据修改-执行
     * @authcheckshow=>2
     */
    public function childupdate_exec(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return cmd(400, $resmsg);
        }
        $reqarr = $request->all();
        $resarr = [];

        $validator = Validator::make($reqarr, [
            'uplockid' => 'bail|required|numeric',
            'id'       => 'bail|required|integer',
            'inmoney'  => 'bail|required|numeric',
            'outcount' => 'bail|required|numeric',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $olddbobj = DB::table('x_fundchild')->where('id', ($reqarr['id'] ?? 0))->first();
        if (empty($olddbobj)) {
            return cmd(400, '【错误】数据不存在，无法修改');
        } else if (($reqarr['uplockid'] ?? '') != ($olddbobj->uplockid ?? '')) {
            return cmd(400, '【错误】数据发生改动，请刷新数据修改页面，获取最新数据后重新执行修改操作');
        } else {
            $errmsg = '';
            $dbarr = [];
            $dbarr['uplockid']        = date('ymdHis') . mt_rand(100000, 999999);
            $dbarr['inmoney']         = round(($reqarr['inmoney'] ?? '0'), 2);
            $dbarr['outcount']        = round(($reqarr['outcount'] ?? '0'), 2);
            $dbarr['update_datetime'] = date('Y-m-d H:i:s');
            $resupd = DB::table('x_fundchild')->where('id', ($reqarr['id'] ?? 0))->update($dbarr);
            if (!$resupd) {
                return cmd(400, '【错误】数据修改失败，系统错误');
            } else {
                ZzAuth::log_cudn('u', __CLASS__, __FUNCTION__, 'x_fundchild', ($reqarr['id'] ?? 0), ZzAuth::data_diff($olddbobj, $dbarr, []));  //记录日志
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
     * @authcheckname=>主表排序ID修改-执行
     * @authcheckshow=>2
     */
    public function orderbyid_update_exec(Request $request)
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return cmd(400, $resmsg);
        }
        $reqarr = $request->all();
        $resarr = [];

        $validator = Validator::make($reqarr, [
            'id' => 'bail|required|integer',
            'orderbyid' => 'bail|required|integer',
        ]);
        if ($validator->fails()) {
            return cmd(400, '【错误】' . $validator->errors()->all()[0]);
        }
        $olddbobj = DB::table($this->tablename)->where('id', ($reqarr['id'] ?? '0'))->first();
        if (empty($olddbobj)) {
            return cmd(400, '【错误】数据不存在');
        } else if (($olddbobj->orderbyid ?? 0) == ($reqarr['orderbyid'] ?? 0)) {
            return cmd(400, '【错误】数据未改变');
        } else {
            $dbarr = [];
            $dbarr['orderbyid']        = $reqarr['orderbyid'] ?? '100';
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
     * @authcheckname=>主表子表更新
     * @authcheckshow=>2
     */
    public function mainchildupdate()
    {
        if (true !== ZzAuth::check_auth(__CLASS__, __FUNCTION__, $resmsg)) {
            return cmd(400, $resmsg);
        }
        //缓存异步触发
        $this->fundtempupdate();

        $dbobj = DB::table($this->tablename)->where('update_datetime', '<=', date('Y-m-d H:i:s', (time() - 20)))->select('id', 'fundcode')->get();
        if (!empty($dbobj)) {
            foreach ($dbobj as $vald) {
                $tmp_id       = $vald->id ?? '';
                $tmp_fundcode = $vald->fundcode ?? '';
                $tmpdbobj = DB::table('x_fundtemp')->where('fundcode', $tmp_fundcode)->first();
                if (!empty($tmpdbobj)) {
                    $tmp_fundname    = $tmpdbobj->fundname ?? '';
                    $tmp_new_shijian = $tmpdbobj->new_shijian ?? '';
                    $tmp_new_jingzhi = $tmpdbobj->new_jingzhi ?? '';
                    $tmp_childarr    = json_decode(($tmpdbobj->jsonstring ?? ''), true);
                    //更新到子表-----------------------------
                    $mincutdateint = (time() - 365 * 86400);
                    $lowupcheck = DB::table('x_fundchild')->where('fid', $tmp_id)->where('create_datetime', '>=', date('Y-m-d', (time() - 12 * 86400)))->first();
                    if (!empty($lowupcheck)) {
                        $mincutdateint = (time() - 12 * 86400);
                    }
                    if (!empty($tmp_childarr)) {
                        foreach ($tmp_childarr as $keyc => $valc) {
                            if (strtotime($keyc) >= $mincutdateint) {
                                $dbarrc = [];
                                $dbarrc['fid'] = $tmp_id;
                                $dbarrc['thedate'] = $keyc;
                                $dbarrc['jingzhi'] = $valc;
                                $dbarrc['create_datetime'] = date('Y-m-d H:i:s');
                                // var_dump($dbarrc);
                                $checkhas = DB::table('x_fundchild')->where('fid', $tmp_id)->where('thedate', $keyc)->first();
                                if (empty($checkhas)) {
                                    DB::table('x_fundchild')->insert($dbarrc);
                                }
                            }
                        }
                    }
                    //更新到子表-结束-----------------------------
                    $childdbobj = DB::table('x_fundchild')->where('fid', $tmp_id)->orderByDesc('thedate')->get();

                    $cdb_sumhas_count = 0;
                    $cdb_sumhas_money = 0;
                    $cdb_sumhas_average = 0;
                    $cdb_sumhas_profit = 0;

                    $cdb_sumhas_incount = 0;
                    $cdb_sumhas_inmoney = 0;
                    $cdb_sumhas_outcount = 0;
                    $cdb_sumhas_outmoney = 0;

                    $cdb_jishuqi = 0;
                    foreach ($childdbobj as $valnc) {
                        ++$cdb_jishuqi;
                        $tmpc_thedate  = $valnc->thedate ?? '';
                        $tmpc_jingzhi  = $valnc->jingzhi ?? 0;
                        $tmpc_inmoney  = $valnc->inmoney ?? 0;
                        $tmpc_incount  = (0 == $tmpc_jingzhi) ? 0 : round(($tmpc_inmoney / $tmpc_jingzhi), 4);
                        $tmpc_outcount = $valnc->outcount ?? 0;
                        $tmpc_outmoney = $tmpc_jingzhi * $tmpc_outcount;
                        $tmpc_sum_count = round(($tmpc_incount - $tmpc_outcount), 2);
                        $tmpc_sum_money = round(($tmpc_inmoney - $tmpc_outmoney), 2);
                        if ($cdb_jishuqi <= 121) {
                            $cdb_jingzhi_day_arr[$cdb_jishuqi] = $tmpc_jingzhi;
                            $cdb_jingzhi_day_arrd[$cdb_jishuqi] = $tmpc_thedate;
                            $cdb_jingzhi_day_arrc[$cdb_jishuqi] = $tmpc_sum_count;
                            $cdb_jingzhi_day_arrm[$cdb_jishuqi] = $tmpc_sum_money;
                        }
                        $cdb_sumhas_incount += $tmpc_incount;
                        $cdb_sumhas_inmoney += $tmpc_inmoney;
                        $cdb_sumhas_outcount += $tmpc_outcount;
                        $cdb_sumhas_outmoney += $tmpc_outmoney;
                    }
                    $cdb_sumhas_incount  = round($cdb_sumhas_incount, 2);
                    $cdb_sumhas_inmoney  = round($cdb_sumhas_inmoney, 2);
                    $cdb_sumhas_outcount = round($cdb_sumhas_outcount, 2);
                    $cdb_sumhas_outmoney = round($cdb_sumhas_outmoney, 2);
                    $cdb_sumhas_count    = round(($cdb_sumhas_incount - $cdb_sumhas_outcount), 2);
                    $cdb_sumhas_money    = round(($cdb_sumhas_inmoney - $cdb_sumhas_outmoney), 2);
                    $cdb_sumhas_average  = (0 == $cdb_sumhas_count) ? 0 : round(($cdb_sumhas_money / $cdb_sumhas_count), 4);
                    $cdb_sumhas_profit   = round(($tmp_new_jingzhi - $cdb_sumhas_average) * $cdb_sumhas_count, 2);

                    $jizhun_jingzhi = 0;
                    if (date('Y-m-d') == substr($tmp_new_shijian, 0, 10)) {
                        $jizhun_jingzhi = $tmp_new_jingzhi;
                    } else {
                        $jizhun_jingzhi = $cdb_jingzhi_day_arr[1] ?? 0;
                    }
                    $arr_jingzhi_zhangdie = [];
                    $cdb_jingzhi_day_arr[0] = $jizhun_jingzhi;
                    $cdb_jingzhi_day_arrd[0] = substr($tmp_new_shijian, 0, 10);
                    for ($iday = 0; $iday <= 120; $iday++) {
                        $tmp_zhangdie_day = round((0 == ($cdb_jingzhi_day_arr[$iday + 1] ?? 0)) ? 0 : ((($cdb_jingzhi_day_arr[$iday] ?? 0) - ($cdb_jingzhi_day_arr[$iday + 1] ?? 0)) / ($cdb_jingzhi_day_arr[$iday + 1] ?? 0) * 100), 2);
                        $tmp_zhangdie_sum = round((0 == ($cdb_jingzhi_day_arr[$iday] ?? 0)) ? 0 : (($jizhun_jingzhi - ($cdb_jingzhi_day_arr[$iday] ?? 0)) / ($cdb_jingzhi_day_arr[$iday] ?? 0) * 100), 2);
                        $tmp_jingzhi      = round(($cdb_jingzhi_day_arr[$iday] ?? 0), 4);
                        $tmp_thedate      = ($cdb_jingzhi_day_arrd[$iday] ?? 0);
                        $tmp_sumcount     = ($cdb_jingzhi_day_arrc[$iday] ?? 0);
                        $tmp_summoney     = ($cdb_jingzhi_day_arrm[$iday] ?? 0);
                        $tmparr = [];
                        $tmparr['zhangdie_day'] = $tmp_zhangdie_day;
                        $tmparr['zhangdie_sum'] = $tmp_zhangdie_sum;
                        $tmparr['jingzhi']      = $tmp_jingzhi;
                        $tmparr['thedate']      = $tmp_thedate;
                        $tmparr['sumcount']     = $tmp_sumcount;
                        $tmparr['summoney']     = $tmp_summoney;
                        $tmparr['sumprofit']    = $this->maincalc($childdbobj, ($iday - 1));
                        $arr_jingzhi_zhangdie[$iday] = $tmparr;
                    }
                    // print_r($arr_jingzhi_zhangdie);

                    $dbarr = [];
                    $dbarr['fundname'] = $tmp_fundname;
                    $dbarr['new_shijian'] = $tmp_new_shijian;
                    $dbarr['new_jingzhi'] = $tmp_new_jingzhi;
                    $dbarr['sumhas_count'] = $cdb_sumhas_count;
                    $dbarr['sumhas_money'] = $cdb_sumhas_money;
                    $dbarr['sumhas_average'] = $cdb_sumhas_average;
                    $dbarr['sumhas_incount'] = $cdb_sumhas_incount;
                    $dbarr['sumhas_inmoney'] = $cdb_sumhas_inmoney;
                    $dbarr['sumhas_outcount'] = $cdb_sumhas_outcount;
                    $dbarr['sumhas_outmoney'] = $cdb_sumhas_outmoney;
                    $dbarr['sumhas_profit'] = $cdb_sumhas_profit;
                    $dbarr['jingzhi_zhangdie_jsonstring'] = json_encode($arr_jingzhi_zhangdie);
                    $dbarr['update_datetime'] = date('Y-m-d H:i:s');

                    DB::table($this->tablename)->where('id', $tmp_id)->update($dbarr);

                    // print_r($dbarr);
                }
            }
        }
    }


    //计算截至对应盈利金额
    private function maincalc(&$childdbobj, $jumpcount = 0)
    {
        if ($jumpcount < 0) {
            $jumpcount = 0;
        }
        $cdb_sumhas_count = 0;
        $cdb_sumhas_money = 0;
        $cdb_sumhas_average = 0;
        $cdb_sumhas_profit = 0;

        $cdb_sumhas_incount = 0;
        $cdb_sumhas_inmoney = 0;
        $cdb_sumhas_outcount = 0;
        $cdb_sumhas_outmoney = 0;

        $jizhun_jingzhi = false;
        $jishuqi = 0;
        foreach ($childdbobj as $valnc) {
            ++$jishuqi;
            $tmpc_jingzhi  = $valnc->jingzhi ?? 0;
            $tmpc_inmoney  = $valnc->inmoney ?? 0;
            $tmpc_incount  = (0 == $tmpc_jingzhi) ? 0 : round(($tmpc_inmoney / $tmpc_jingzhi), 4);
            $tmpc_outcount = $valnc->outcount ?? 0;
            $tmpc_outmoney = $tmpc_jingzhi * $tmpc_outcount;

            if ($jishuqi > $jumpcount) {
                if (false === $jizhun_jingzhi) {
                    $jizhun_jingzhi = $tmpc_jingzhi;
                }
                $cdb_sumhas_incount += $tmpc_incount;
                $cdb_sumhas_inmoney += $tmpc_inmoney;
                $cdb_sumhas_outcount += $tmpc_outcount;
                $cdb_sumhas_outmoney += $tmpc_outmoney;
            }
        }
        $cdb_sumhas_incount = round($cdb_sumhas_incount, 2);
        $cdb_sumhas_inmoney = round($cdb_sumhas_inmoney, 2);
        $cdb_sumhas_outcount = round($cdb_sumhas_outcount, 2);
        $cdb_sumhas_outmoney = round($cdb_sumhas_outmoney, 2);
        $cdb_sumhas_count = round(($cdb_sumhas_incount - $cdb_sumhas_outcount), 2);
        $cdb_sumhas_money = round(($cdb_sumhas_inmoney - $cdb_sumhas_outmoney), 2);
        $cdb_sumhas_average = (0 == $cdb_sumhas_count) ? 0 : round(($cdb_sumhas_money / $cdb_sumhas_count), 4);
        $cdb_sumhas_profit = round(($jizhun_jingzhi - $cdb_sumhas_average) * $cdb_sumhas_count, 2);
        return $cdb_sumhas_profit;
    }

    //基金缓存数据更新
    private function fundtempupdate()
    {
        $start_time = microtime(true);
        $cuttimes = 120;
        $nowhour = date('G');
        $nowweek = date('w');
        if ($nowhour >= 9 && $nowhour <= 14 && $nowweek > 0 && $nowweek < 6) {
            $cuttimes = 30;
        }
        $succon = 0;
        $dbobj = DB::table($this->tablename)->select('fundcode')->groupBy('fundcode')->get();
        if (!empty($dbobj)) {
            foreach ($dbobj as $vald) {
                $tmp_fundcode = $vald->fundcode ?? '';
                if (6 == strlen($tmp_fundcode)) {
                    //判断更新是否频繁
                    $datahas = DB::table('x_fundtemp')->where('fundcode', $tmp_fundcode)->first();
                    if (empty($datahas) || strtotime($datahas->update_datetime ?? '2000-01-01') < (time() - $cuttimes)) {
                        $fund_mainurl = 'https://fundgz.1234567.com.cn/js/' . $tmp_fundcode . '.js?rt=' . time() . mt_rand(100, 999);
                        $res_main = zzget($fund_mainurl);
                        $resarr_main = json_decode((!empty($res_main) ? substr($res_main, 8, -2) : ''), true);

                        $fund_childurl = 'https://fund.eastmoney.com/pingzhongdata/' . $tmp_fundcode . '.js?v=' . date('YmdHis');
                        $reschild = zzget($fund_childurl);
                        $tmpa = explode('var Data_ACWorthTrend = ', $reschild, 2);
                        $tmpb = explode(';', ($tmpa[1] ?? ''), 2);
                        $resarr_child = json_decode(($tmpb[0] ?? ''), true);

                        if (!empty($resarr_main) && !empty($resarr_child)) {
                            ++$succon;
                            $tmpchildarr = [];
                            foreach ($resarr_child as $valc) {
                                $tmpdate    = date('Y-m-d', (round(($valc[0] ?? 0) / 1000)));
                                $tmpjingzhi = $valc[1] ?? 0;
                                $tmpchildarr[$tmpdate] = $tmpjingzhi;
                            }
                            $dbarr = [];
                            $dbarr['fundcode']    = $tmp_fundcode;
                            $dbarr['fundname']    = $resarr_main['name'] ?? '';
                            $dbarr['new_shijian'] = $resarr_main['gztime'] ?? '';
                            // $dbarr['new_shijian'] = substr(($resarr_main['gztime'] ?? ''), -8);
                            $dbarr['new_jingzhi'] = $resarr_main['gsz'] ?? '0';
                            $dbarr['jsonstring']  = json_encode($tmpchildarr);

                            $checkhas = DB::table('x_fundtemp')->where('fundcode', $tmp_fundcode)->first();
                            if (!empty($checkhas)) {
                                $dbarr['update_datetime'] = date('Y-m-d H:i:s');
                                $resupd = DB::table('x_fundtemp')->where('fundcode', $tmp_fundcode)->update($dbarr);
                            } else {
                                $dbarr['create_datetime'] = date('Y-m-d H:i:s');
                                $resupd = DB::table('x_fundtemp')->insert($dbarr);
                            }
                        } else {
                        }
                    } else {
                        //跳过
                    }
                }
            }
        }

        //上证缓存数据入库
        $tmpidname = 'shangzhengzhishushow';
        $tmptable  = 'x_tmpdata';
        $tmpdbobj  = DB::table($tmptable)->where('tmpidname', $tmpidname)->first();
        if (empty($tmpdbobj) || (!empty($tmpdbobj) && (strtotime($tmpdbobj->update_datetime ?? '2000-01-01') < (time() - $cuttimes)))) {
            //缓存上证指数
            $shangzhengurl = 'http://push2his.eastmoney.com/api/qt/stock/trends2/get?cb=&secid=1.000001&ut=&fields1=f1%2Cf2%2Cf3%2Cf4%2Cf5%2Cf6%2Cf7%2Cf8%2Cf9%2Cf10%2Cf11&fields2=f51%2Cf53%2Cf56%2Cf58&iscr=0&ndays=1&_='.time().mt_rand(100,999);
            $resshangzheng = zzget($shangzhengurl);
            $resarr_shangzheng = json_decode(($resshangzheng ?? ''), true);
            $endtmparr         = ($resarr_shangzheng['data']['trends'] ?? []);
            $tmpcc             = end($endtmparr);
            $tmpcccarr         = explode(',', $tmpcc);
            $tmparr = [];
            $tmparr['pre_lastclose'] = $resarr_shangzheng['data']['preClose'] ?? '0';
            $tmparr['pre_thedate']   = date('Y-m-d', $resarr_shangzheng['data']['time'] ?? '0');
            $tmparr['pre_newvalue']  = $tmpcccarr[1] ?? 0;
            $tmparr['pre_newdate']   = $tmpcccarr[0] ?? '';
            $tmparr['pre_zhangdie']   = (0 == $tmparr['pre_lastclose']) ? 0 : (round(($tmparr['pre_newvalue'] - $tmparr['pre_lastclose']) / $tmparr['pre_lastclose'], 4) * 100);
            $tmparr['pre_zhangdieval']   = round(($tmparr['pre_newvalue'] - $tmparr['pre_lastclose']), 2);
            $showdata = '';
            if ((date('Y-m-d') == $tmparr['pre_thedate']) && $nowhour >= 9 && $nowhour <= 15) {
                $showdata .= '<span style="color:blue;">';
            } else {
                $showdata .= '<span>';
            }
            $showdata .= '上证指数：' . substr($tmparr['pre_newdate'], -11) . '&nbsp;&nbsp;&nbsp;之前：' . $tmparr['pre_lastclose'] . '&nbsp;&nbsp;&nbsp;最新：' . $tmparr['pre_newvalue'] . ' / ' . $tmparr['pre_zhangdieval'] . '&nbsp;&nbsp;&nbsp;涨跌幅：' . $tmparr['pre_zhangdie'] . '%';
            $showdata .= '</span>';
            if (!empty($tmpdbobj)) {
                $dbarr = [];
                $dbarr['tmpdata']         = $showdata;
                $dbarr['update_datetime'] = date('Y-m-d H:i:s');
                DB::table($tmptable)->where('tmpidname', $tmpidname)->update($dbarr);
            } else {
                $dbarr = [];
                $dbarr['tmpidname']       = $tmpidname;
                $dbarr['tmpdata']         = $showdata;
                $dbarr['create_datetime'] = date('Y-m-d H:i:s');
                DB::table($tmptable)->insert($dbarr);
            }
        }
        $newtmpdbobj   = DB::table($tmptable)->where('tmpidname', $tmpidname)->first();
        if (!empty($newtmpdbobj)) {
            echo ($newtmpdbobj->tmpdata ?? '') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }



        $haoshitime = round((microtime(true) - $start_time) * 1000);
        echo '缓存完成 ' . date('Y-m-d H:i:s') . '，缓存条数：' . $succon . '/' . count($dbobj) . '，耗时：' . $haoshitime . '毫秒';
    }
}
