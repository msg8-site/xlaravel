<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PragmaRX\Google2FA\Google2FA;

class ZzAuth
{

    //菜单列表返回封装
    public static function menulist()
    {
        $resarr = [
            [
                'm' => '账号操作',
                'c' => [
                    ['平台主页', 'iframebody'],
                    ['修改密码', 'passwordchange'],
                    ['动态码绑定', 'googlecodebind'],
                    ['好网址导航', 'https://www.msg8.site/'],
                    ['退出登录', 'logout'],
                ]
            ],
        ];
        if ('' == session(SESS_USERNAME, '')) {
            return $resarr;
        } else {
            //查询用户对应数据
            $userobj = DB::table('sys_user')->where('username', session(SESS_USERNAME, ''))->first();
            if (empty($userobj)) {
                return $resarr;
            } else {
                $user_role = $userobj->role ?? '0';
                $roleinarr = [];
                $roledbobj = DB::table('sys_role')->where('id', $user_role)->first();
                if (!empty($roledbobj)) {
                    $roleinarr = comm_strintoarr($roledbobj->menuidstr ?? '');
                }
                $DB = DB::table('sys_menu');
                if ('root' != session(SESS_USERNAME, '')) {
                    $DB->whereIn('id', $roleinarr);
                }
                $menuobj = $DB->orderBy('fid', 'asc')->orderBy('orderbyid', 'desc')->get();
                if (empty($menuobj)) {
                    return $resarr;
                } else {
                    $menuarr = [];
                    foreach ($menuobj as $valm) {
                        $menu_id = $valm->id ?? 0;
                        $menu_fid = $valm->fid ?? null;
                        $menu_menuname = $valm->menuname ?? '';
                        $menu_menupath = $valm->menupath ?? '';
                        if (null !== $menu_fid && $menu_id > 0) {
                            if (0 == $menu_fid) {
                                //根节点
                                if (!isset($menuarr[$menu_id])) {
                                    $menuarr[$menu_id]['m'] = $menu_menuname;
                                }
                            } else {
                                if (isset($menuarr[$menu_fid])) {
                                    $menuarr[$menu_fid]['c'][] = [$menu_menuname, $menu_menupath, $menu_fid, $menu_id];
                                }
                            }
                        }
                    }
                    array_unshift($menuarr, $resarr[0]);
                    return $menuarr;
                }
            }
        }
    }

    //是否已登录登陆验证
    public static function check_auth($classname = '', $functionname = '', &$errmsg = ''): bool
    {
        $log = new Logger('zzcheck');
        $log->pushHandler(new StreamHandler('/data/log/' . COMM_SYSFLAG . '/' . date('Y_m_d') . '_' . COMM_SYSFLAG, Logger::INFO));

        $resval = false;
        $user_role = '0';
        $user_moreclientflag = null;
        $user_moreclientkey = null;
        $checknodename = null;
        if ('' == session(SESS_USERNAME, '')) {
            $errmsg = '【错误】用户账号未登录';
            $resval = false;
        } else {
            //查询用户对应数据
            $userobj = DB::table('sys_user')->where('username', session(SESS_USERNAME, ''))->first();
            if (empty($userobj)) {
                $errmsg = '【错误】用户数据不存在';
                $resval = false;
            } else {
                $user_role = $userobj->role ?? '0';
                $user_moreclientflag = $userobj->moreclientflag ?? '1';
                $user_moreclientkey = $userobj->moreclientkey ?? '';
                $user_usergooglekey = $userobj->usergooglekey ?? '';
                //多客户端登录判断
                if (1 == $user_moreclientflag && session(SESS_MORECKEY, '') != $user_moreclientkey) {
                    $errmsg = '【错误】当前用户已在其他客户端登陆，请重新登陆';
                    $resval = false;
                } else {
                    if ('google' == COMM_CODETYPE && '' == $user_usergooglekey && strtolower('IndexController') != strtolower(comm_getclassname($classname))) {
                        $errmsg = '【提示】系统登陆要求验证谷歌动态码，当前页面需要绑定谷歌动态码后才可访问';
                        $resval = false;
                    } else {
                        if ('root' == session(SESS_USERNAME, '')) {
                            //超级管理员放行全部权限
                            $resval = true;
                        } else {
                            //查询角色表
                            $roleobj = DB::table('sys_role')->where('id', $user_role)->first();
                            if (empty($roleobj)) {
                                $errmsg = '【错误】角色数据不存在';
                                $resval = false;
                            } else {
                                $role_nodeidstr = $roleobj->nodeidstr ?? '';
                                //查询该角色允许的节点数据
                                $nodeobj =  DB::table('sys_node')
                                    ->where('classname', comm_getclassname($classname))
                                    ->where('functionname', $functionname)
                                    ->whereIn('id', comm_strintoarr($role_nodeidstr))
                                    ->first();
                                if (empty($nodeobj)) {
                                    $errmsg = '【错误】该角色没有页面节点对应的访问权限';
                                    $resval = false;
                                } else {
                                    //通过
                                    $checknodename = $nodeobj->nodename ?? '';
                                    $resval = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        $log->warning(date('Y-m-d H:i:s') . '---' . comm_getip() . '---' . session(SESS_USERNAME, '') . '---' . $user_moreclientkey . '---' . $classname . '---' . $functionname . '---' . $checknodename . '---' . $errmsg);
        return $resval;
    }


    //登陆验证封装
    public static function check_login($arr = [], &$showmsg = ''): bool
    {
        if (!is_array($arr) || count($arr) <= 0) {
            return false;
        } else {
            //查询该IP是否频繁，180天内单个IP错误次数不超1024
            $ipcount = DB::table('log_userlogin')->where('loginip', comm_getip())->where('create_datetime', '>=', date('Y-m-d H:i:s', (time() - 180 * 86400)))->where('flag', '2')->count();
            if ($ipcount > 1024) {
                $showmsg = '【错误】IP地址登陆频繁，无法登陆-180天错误超过1024次';
                return false;
            } else {
                //查询该IP是否频繁，15内单个IP错误次数不超10
                $ipcount = DB::table('log_userlogin')->where('loginip', comm_getip())->where('create_datetime', '>=', date('Y-m-d H:i:s', (time() - 15 * 60)))->where('flag', '2')->count();
                if ($ipcount > 10) {
                    $showmsg = '【错误】IP地址登陆频繁，无法登陆-15分钟错误超过10次';
                    return false;
                } else {
                    //查询数据库获取用户数据
                    $userarr = DB::table('sys_user')->where('username', ($arr['username'] ?? ''))->first();
                    if (empty($userarr)) {
                        $showmsg = '【错误】用户名不存在';
                        return false;
                    } else {
                        $user_id             = $userarr->id ?? '';
                        $user_status         = $userarr->status ?? '';
                        $user_role           = $userarr->role ?? '';
                        $user_username       = $userarr->username ?? '';
                        $user_usergooglekey  = $userarr->usergooglekey ?? '';
                        $user_passwd         = $userarr->passwd ?? '';
                        $user_moreclientflag = $userarr->moreclientflag ?? '';

                        if ('root' != $user_username && '1' != $user_status) {
                            $showmsg = '【错误】账号未启用，无法登陆，请联系管理员';  //管理员账号不可冻结
                            return false;
                        } else {
                            //验证码验证
                            if ('google' == COMM_CODETYPE) {
                                //谷歌动态码
                                if ('' != $user_usergooglekey) {
                                    $rr = ZzAuth::funchk_gcodecheck('userlogin', $user_usergooglekey, ($arr['checkcode'] ?? ''), $user_username);
                                    if (true !== $rr) {
                                        ZzAuth::log_loginwrite('2', $user_username, comm_getip(), $rr);
                                        $showmsg = '【错误】' . $rr;
                                        return false;
                                    } else {
                                        //通过验证码判断
                                    }
                                } else {
                                    //验证谷歌动态码针对未绑定的用户跳过验证
                                    $showmsg = '<br>【提示】该账号未配置谷歌动态码，登陆跳过动态码验证操作<br>登陆后请尽快完成动态码绑定操作';
                                    // return false;
                                }
                            } else {
                                //图形验证码
                                $sess_loginvcodetime = session(COMM_SYSFLAG . '_loginvcodetime');
                                $sess_loginvcodestr = session(COMM_SYSFLAG . '_loginvcodestr');
                                if ('' == $sess_loginvcodestr || '' == $sess_loginvcodestr) {
                                    $showmsg = '【错误】请先获取新的图形验证码';
                                    return false;
                                } else if ((time() - $sess_loginvcodetime) > 3 * 60) {
                                    $showmsg = '【错误】图形验证码超过有效期，请重新获取';
                                    return false;
                                } else if (($arr['checkcode'] ?? '') != $sess_loginvcodestr) {
                                    ZzAuth::log_loginwrite('2', $user_username, comm_getip(), '图形验证码验证失败');
                                    $showmsg = '【错误】图形验证码验证失败';
                                    return false;
                                } else {
                                    //通过验证码判断
                                    session([COMM_SYSFLAG . '_loginvcodetime' => null]);
                                    session([COMM_SYSFLAG . '_loginvcodestr' => null]);
                                }
                            }
                            //验证密码
                            if (hash('sha256', ($user_passwd . ($arr['randstr'] ?? ''))) != ($arr['password'] ?? '')) {
                                ZzAuth::log_loginwrite('2', $user_username, comm_getip(), '用户密码验证失败');
                                $showmsg = '【错误】用户密码验证失败';
                                return false;
                            } else {
                                //更新最新的登陆时间和IP到数据库
                                $dbarr = [];
                                $dbarr['update_datetime'] = date('Y-m-d H:i:s');
                                $dbarr['moreclientkey'] = uniqid() . mt_rand(10000, 99999);
                                $r = DB::table('sys_user')->where('username', $user_username)->update($dbarr);
                                if (!$r) {
                                    $showmsg = '【错误】系统内部，请稍后重试';
                                    return false;
                                } else {
                                    //设置用户名和权限session
                                    session([SESS_USERNAME => $user_username]);
                                    session([SESS_MORECKEY => $dbarr['moreclientkey']]);
                                    ZzAuth::log_loginwrite('1', $user_username, comm_getip(), '用户登陆成功');
                                    //登陆成功
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    //清理session退出
    public static function logout()
    {
        session([SESS_USERNAME => '']);
        session([SESS_MORECKEY => '']);
        return true;
    }

    //动态码验证封装
    public static function funchk_gcodecheck($describename = '', $usergooglekey = '', $checkcode = '', $checkusername = 'test')
    {
        if ('' == $describename || '' == $usergooglekey || strlen($usergooglekey) < 16 || strlen($usergooglekey) > 128 || '' == $checkcode || '' == $checkusername) {
            return '系统参数验证出错，系统错误';
        } else {
            //判断120秒内，单个用户的验证码是否使用过
            $chkarr = DB::table('log_codecheck')
                ->where('create_datetime', '>=', date('Y-m-d H:i:s', (time() - 120)))
                ->where('create_datetime', '<=', date('Y-m-d H:i:s', (time() + 60)))
                ->where('checkusername', $checkusername)
                ->where('checkcode', $checkcode)
                ->first();
            if (!empty($chkarr)) {
                return '该动态码已被使用，请等待下一个动态码';
            } else {
                //对单用户验证错误次数进行判断，10分钟最大20次，1小时内最大60次，24小时内最大360次，防止单用户的暴力破解
                $chkcon600 = DB::table('log_codecheck')
                    ->where('create_datetime', '>=', date('Y-m-d H:i:s', (time() - 600)))
                    ->where('create_datetime', '<=', date('Y-m-d H:i:s', (time() + 60)))
                    ->where('describename', $describename)
                    ->where('checkusername', $checkusername)
                    ->where('checkflag', '2')
                    ->count();
                if ($chkcon600 > 20) {
                    return '账号动态码错误次数超限，近10分钟内累计错误达到20次';
                } else {
                    $chkcon3600 = DB::table('log_codecheck')
                        ->where('create_datetime', '>=', date('Y-m-d H:i:s', (time() - 3600)))
                        ->where('create_datetime', '<=', date('Y-m-d H:i:s', (time() + 60)))
                        ->where('describename', $describename)
                        ->where('checkusername', $checkusername)
                        ->where('checkflag', '2')
                        ->count();
                    if ($chkcon3600 > 60) {
                        return '账号动态码错误次数超限，近1小时内累计错误达到60次';
                    } else {
                        $chkcon86400 = DB::table('log_codecheck')
                            ->where('create_datetime', '>=', date('Y-m-d H:i:s', (time() - 86400)))
                            ->where('create_datetime', '<=', date('Y-m-d H:i:s', (time() + 60)))
                            ->where('describename', $describename)
                            ->where('checkusername', $checkusername)
                            ->where('checkflag', '2')
                            ->count();
                        if ($chkcon86400 > 360) {
                            return '账号动态码错误次数超限，近24小时内累计错误达到360次';
                        } else {
                            //谷歌动态码验证
                            $Google2FA = new Google2FA();
                            $resgoo = $Google2FA->verify($checkcode, $usergooglekey, 3);

                            //插入数据库
                            $dbarr = [];
                            $dbarr['checkflag']       = ((true === $resgoo) ? '1' : '2');
                            $dbarr['create_datetime'] = date('Y-m-d H:i:s');
                            $dbarr['describename']    = $describename;
                            $dbarr['checkusername']   = $checkusername;
                            $dbarr['checkuserip']     = comm_getip();
                            $dbarr['checkcode']       = $checkcode;
                            $dbarr['moreclientkey']   = session(SESS_MORECKEY, '');

                            $r = DB::table('log_codecheck')->insert($dbarr);
                            if (!$r) {
                                return '数据插入失败，系统错误';
                            } else {
                                if (true === $resgoo) {
                                    return true;
                                } else {
                                    return '动态码验证失败，请重试';
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 登陆日志写入数据库
     *
     * @param string $loginflag
     * @param string $loginname
     * @param string $loginip
     * @param string $errdescribe
     * @return boolean
     */
    public static function log_loginwrite($loginflag = '', $loginname = '', $loginip = '', $errdescribe = ''): bool
    {
        if ('' == $loginname) {
            return false;
        } else {
            $dbarr = [];
            $dbarr['flag']            = $loginflag;                       //1登录成功，2登录失败
            $dbarr['loginname']       = $loginname;
            $dbarr['loginip']         = $loginip;
            $dbarr['errdescribe']     = $errdescribe;
            $dbarr['useragent']       = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $dbarr['moreclientkey']   = session(SESS_MORECKEY, '');
            $dbarr['create_datetime'] = date('Y-m-d H:i:s');
            DB::table('log_userlogin')->insert($dbarr);
            return true;
        }
    }

    /**
     * 数据比较格式整理
     *
     * @param array $oldarr
     * @param array $newarr
     * @return void
     */
    public static function data_diff($oldarr = [], $newarr = [], $jumparr = [])
    {
        if (is_object($oldarr)) {
            $oldarr = get_object_vars($oldarr);
        }
        if (is_object($newarr)) {
            $newarr = get_object_vars($newarr);
        }
        if (!is_array($oldarr)) {
            $oldarr = [];
        }
        if (!is_array($newarr)) {
            $newarr = [];
        }
        if (!is_array($jumparr)) {
            $jumparr = [];
        }
        $resarr = [];
        foreach ($newarr as $keyn => $valn) {
            $oldval = $oldarr[$keyn] ?? '';
            if (!in_array($keyn, $jumparr) && (string)$oldval !== (string)$valn) {
                $resarr[$keyn] = $oldarr[$keyn] . '=>' . $valn;
            }
        }
        if (count($resarr) <= 0) {
            return '';
        } else {
            return json_encode($resarr, JSON_UNESCAPED_UNICODE);
        }
    }
    /**
     * 数据格式整理
     *
     * @param array $newarr
     * @param array $jumparr
     * @return void
     */
    public static function data_tojstr($newarr = [], $jumparr = [])
    {
        if (is_object($newarr)) {
            $newarr = get_object_vars($newarr);
        }
        if (!is_array($newarr)) {
            $newarr = [];
        }
        if (!is_array($jumparr)) {
            $jumparr = [];
        }
        $resarr = [];
        foreach ($newarr as $keyn => $valn) {
            if (!in_array($keyn, $jumparr)) {
                $resarr[$keyn] = $valn;
            }
        }
        if (count($resarr) <= 0) {
            return '';
        } else {
            return json_encode($resarr, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 数据库操作数据日志写入
     *
     * @param string $type
     * @param string $classname
     * @param string $functionname
     * @param string $tablename
     * @param integer $tableid
     * @param string $curndata
     * @return bool
     */
    public static function log_cudn($type = 'n', $classname = '', $functionname = '', $tablename = '', $tableid = 0, $cudndata = '')
    {
        $dbarr                     = [];
        $dbarr['type']             = $type;                          //日志类型，c添加，u修改，d删除，n其他
        $dbarr['username']         = session(SESS_USERNAME, '');
        $dbarr['userip']           = comm_getip();
        $dbarr['moreclientkey']    = session(SESS_MORECKEY, '');
        $dbarr['cudnclassname']    = comm_getclassname($classname);
        $dbarr['cudnfunctionname'] = $functionname;
        $dbarr['tablename']        = $tablename;
        $dbarr['tableid']          = $tableid;
        $dbarr['cudndata']         = $cudndata;
        $dbarr['create_datetime']  = date('Y-m-d H:i:s');
        DB::table('log_databasecudn')->insert($dbarr);
        return true;
    }
}
