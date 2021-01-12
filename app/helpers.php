<?php

//全局常量定义
define('COMM_SYSFLAG', basename((dirname(__DIR__))));  //定义系统标识名，用于session前缀等标识
define('COMM_SECIP', true);  //是否为双层ip，默认 false 仅判断REMOTE_ADDR
define('COMM_DBLOCK', false);  //数据库迁移回滚锁，防止误删除， true 允许回滚删除数据表，其他值如 false ，不允许回滚删除
define('COMM_SYSNAME', 'xlaravel后台框架');  //平台名称
define('COMM_CODETYPE', 'imgcode');  //验证类型， google 为谷歌动态码， imgcode 为图形验证码

//定义存储session的变量名称
define('SESS_USERNAME', COMM_SYSFLAG . '_username');  //用户名
define('SESS_MORECKEY', COMM_SYSFLAG . '_moreclientkey');  //用户登录标识


//ajax参数返回
function cmd($c = -1, $m = '', $d = [])
{
    $resarr = [];
    $resarr['c'] = $c; //200为成功，400为失败
    $resarr['m'] = $m; //返回消息
    $resarr['d'] = $d;
    return $resarr;
}

//随机密码生成
function comm_randpasswd($maxlen = 16)
{
    $str = 'QWERTYUIPASDFGHJKXCVBNMqwertyuipsdfghjkzxcvbnm123456789';
    $arr = str_split($str);
    $len = count($arr) - 1;
    $randpasswd = '';
    for ($i = 0; $i < $maxlen; $i++) {
        $randpasswd .= $arr[mt_rand(0, $len)];
    }
    return $randpasswd;
}

//下拉框参数封装
function comm_ccoption($chkval = '', $arrc = [], $hassel = true)
{
    $optionmy = '<option value="">请选择</option>';
    if (true !== $hassel) {
        $optionmy = '';
    }
    $myarr = [];
    if (is_array($arrc)) {
        $myarr = $arrc;
    } else {
        if ('typestatus' == $arrc) {
            $myarr = [
                '1' => '1-开启',
                '2' => '2-关闭',
                '4' => '4-禁用',
            ];
        } else if ('moreclient' == $arrc) {
            $myarr = [
                '1' => '1-单终端',
                '2' => '2-多终端',
            ];
        } else if ('openclose' == $arrc) {
            $myarr = [
                '1' => '1-开放',
                '2' => '2-封闭',
            ];
        }
    }
    foreach ($myarr as $keym => $valm) {
        $optionmy .= '<option value="' . $keym . '" ';
        if ((string)$chkval === (string)$keym) {
            $optionmy .= ' selected="selected" ';
        }
        $optionmy .= '>' . $valm . '</option>';
    }
    return $optionmy;
}

//基金涨跌颜色
function comm_fundcolor($value = 0)
{
    if ($value >= 0) {
        return '<span style="color:blue">' . $value . '</span>';
    } else {
        return '<span style="color:red">' . $value . '</span>';
    }
}
//状态标签封装返回
function comm_colorspan($status = 0)
{
    if (1 == $status) {
        return comm_spanstr('开启', 'green');
    } else if (2 == $status) {
        return comm_spanstr('关闭', 'red');
    } else if (4 == $status) {
        return comm_spanstr('禁用', 'gray');
    } else {
        return $status;
    }
}

//状态标签封装返回
function comm_colorspantype($status = '')
{
    if ('c' == $status) {
        return comm_spanstr('添加', 'green');
    } else if ('u' == $status) {
        return comm_spanstr('修改', 'blue');
    } else if ('d' == $status) {
        return comm_spanstr('删除', 'red');
    } else if ('n' == $status) {
        return comm_spanstr('其他', 'gray');
    } else {
        return $status;
    }
}
/**
 * 标签背景颜色匹配
 *
 * @param string $msg
 * @param string $color green|red|blue|gray|orange|black
 * @return void
 */
function comm_spanstr($msg = '', $color = '')
{
    if ('green' == $color) {
        return '<span class="layui-badge" style="background-color:#66CDAA">' . $msg . '</span>';
    } else if ('red' == $color) {
        return '<span class="layui-badge" style="background-color:#F09090">' . $msg . '</span>';
    } else if ('blue' == $color) {
        return '<span class="layui-badge" style="background-color:#6BB7FA">' . $msg . '</span>';
    } else if ('gray' == $color) {
        return '<span class="layui-badge" style="background-color:#AAAAAA">' . $msg . '</span>';
    } else if ('orange' == $color) {
        return '<span class="layui-badge" style="background-color:#FFCC6F">' . $msg . '</span>';
    } else if ('black' == $color) {
        return '<span class="layui-badge" style="background-color:#555555">' . $msg . '</span>';
    } else {
        return '<span class="layui-badge-rim">' . $msg . '</span>';
    }
}

/**
 * 注释解析函数
 *
 * @param string $docstr
 * @return void
 */
function comm_noteanalysis($docstr = '')
{
    $resarr = [];
    $tmparr = explode("\n", $docstr);
    foreach ($tmparr as $valt) {
        if (false !== strpos($valt, '@authcheck')) {
            $tmpbrr = explode('@', $valt, 2);
            $tmpcc = explode('=>', end($tmpbrr), 2);
            if ('' != ($tmpcc[0] ?? '')) {
                $resarr[($tmpcc[0] ?? '')] = $tmpcc[1] ?? '';
            }
        }
    }
    return $resarr;
}

//获取无命名空间的类名
function comm_getclassname($classstr = '')
{
    $classstr = trim($classstr);
    $tmparr = explode('\\', $classstr);
    return end($tmparr);
}

//in字符串分割为数组
function comm_strintoarr($str = '')
{
    $str = trim($str);
    if ('' == $str) {
        return [];
    } else {
        $resarr = [];
        $tmparr = explode(',', $str);
        foreach ($tmparr as $valt) {
            $valt = trim($valt);
            if (is_numeric($valt)) {
                array_push($resarr, intval($valt));
            }
        }
        return $resarr;
    }
}

//获取用户的实际IP地址
function comm_getip()
{
    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'] ?? '';
    $HTTP_X_REAL_IP = $_SERVER['HTTP_X_REAL_IP'] ?? '';
    $returnip = '0.0.0.0';
    if (true === COMM_SECIP && '' != $HTTP_X_REAL_IP) {
        $returnip = $HTTP_X_REAL_IP;
    } else {
        $returnip = $REMOTE_ADDR;
    }
    //IP地址合法验证
    if ('' != $returnip && filter_var($returnip, FILTER_VALIDATE_IP)) {
        return $returnip;
    } else {
        return '0.0.0.0';
    }
}

function zzget($url = '', $timeout = 5000, $header = array(), $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36 (https://www.msg8.site)', $restype = 'one')
{
    if (!function_exists('curl_init')) {
        return false;
    }
    if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
        return 'url_error';
    }
    //对传递的header数组进行整理
    $headerArr = array();
    foreach ($header as $n => $v) {
        $headerArr[] = $n . ':' . $v;
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_NOBODY, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    if (trim($useragent) != '') {
        curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
    }
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
    curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeout);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    if (count($headerArr) > 0) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArr);
    }
    $content  = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $run_time = (curl_getinfo($curl, CURLINFO_TOTAL_TIME) * 1000);
    $errorno  = curl_errno($curl);
    curl_close($curl);
    if ('one' == $restype) {
        return $content;
    }
    //定义return数组变量
    $retarr = array();
    $retarr['content']  = $content;
    $retarr['httpcode'] = $httpcode;
    $retarr['run_time'] = $run_time;
    $retarr['errorno']  = $errorno;
    return $retarr;
}
