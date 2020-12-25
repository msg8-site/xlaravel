<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Parsedown;

//主页面开放文档
class PublicdocController extends Controller
{
    private $tablename = 'doc_markdown';
    private $viewarray = [];

    public function __construct()
    {
        //视图文件路径数组
        $this->viewarray = [
            'docshow'     => 'publicdoc/page_docshow',
            'childiframe' => 'publicdoc/childiframe',
            'leftmenu'    => 'publicdoc/leftmenu',
        ];
    }

    //开放文档子框架
    public function childiframe(Request $request)
    {
        $reqarr = $request->all();
        $resarr = [];

        return view(($this->viewarray[__FUNCTION__] ?? 'system/error'), ['title'=>'好网址文档','reqarr' => $reqarr, 'resarr' => $resarr]);
    }

    //开放文档子框架菜单
    public function leftmenu(Request $request)
    {
        $reqarr = $request->all();
        $resarr = [];

        $menulist = [];
        $DB = DB::table($this->tablename)->select('id', 'flag', 'docname', 'typename', 'orderbyid')->where('flag', '1');
        $dbobj = $DB->orderBy('typename', 'asc')->orderBy('orderbyid', 'desc')->get();
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

    //开放文档子框架主页
    public function rightbody(Request $request)
    {
        $reqarr = $request->all();
        $resarr = [];
        echo '<h1 style="margin:30px;">请点击左侧菜单查看文档</h1>';
    }

    //开放文档搜索
    public function search(Request $request)
    {
        $reqarr = $request->all();
        $resarr = [];

        $menulist = [];
        $DB = DB::table($this->tablename)->select('id', 'flag', 'docname', 'typename', 'orderbyid')->where('flag', '=', '1')->where(function ($query) use (&$reqarr) {
            if ('' != ($reqarr['searchval'] ?? '')) {
                $query->orWhere('docname', 'like', '%' . ($reqarr['searchval'] ?? '') . '%');
            }
            if ('' != ($reqarr['searchval'] ?? '')) {
                $query->orWhere('typename', 'like', '%' . ($reqarr['searchval'] ?? '') . '%');
            }
            if ('' != ($reqarr['searchval'] ?? '')) {
                $query->orWhere('content', 'like', '%' . ($reqarr['searchval'] ?? '') . '%');
            }
        });

        $dbobj = $DB->orderBy('typename', 'asc')->orderBy('orderbyid', 'desc')->get();
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
                    $showhtml .= '<dd><a class="childmenuname" href="publicdoc_docshow?id=' . $valmc[1] . '" target="iframeright">' . $valmc[0] . '</a></dd>';
                }
                $showhtml .= '</dl>';
            }
            $showhtml .= '</li>';
        }
        return cmd(200, '数据添加成功', $showhtml);
    }

    //开放文档搜索查看
    public function docshow(Request $request)
    {
        $reqarr = $request->all();
        $resarr = [];
        $resarr['errmsg'] = '';  //不为空时页面直接仅显示本信息
        if (!is_numeric($reqarr['id'] ?? '')) {
            $resarr['errmsg'] = '【错误】非法操作，未匹配到对应数据';
        } else {
            $DB = DB::table($this->tablename);
            $dbobj = $DB->where('id', ($reqarr['id'] ?? '0'))->where('flag', '1')->first();
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
}
