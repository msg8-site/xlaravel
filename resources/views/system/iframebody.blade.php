@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {


    });
</script>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <h3 style="color: #1E9FFF;font-weight:bold;">当前用户账号信息</h3>
        <hr class="layui-bg-gray">
        <div class="layui-row">
                <table class="layui-table">
                    <colgroup>
                        <col width="150">
                        <col>
                    </colgroup>
                    <tr>
                        <td style="text-align: right;">用户ID</td>
                        <td>{{$userobj->id??''}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">用户名</td>
                        <td>{{$userobj->username??''}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">用户昵称</td>
                        <td>{{$userobj->nickname??''}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">角色名称</td>
                        <td>{{$userobj->rolename??''}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">动态码绑定</td>
                        <td>{!!$userobj->usergooglekey??''!!}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">多端限制</td>
                        <td>{!!$userobj->moreclientflag??''!!}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">最新多端标识</td>
                        <td>{{$userobj->moreclientkey??''}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">当前登陆标识</td>
                        <td><b>{{session(SESS_MORECKEY, '')}}</b></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">推荐导航网址</td>
                        <td> <a href="https://www.msg8.site/" target="_blank" style="color:blue;">好网址导航</a></td>
                    </tr>
                </table>
        </div>
        <br><br>
        <h3 style="color: #1E9FFF;font-weight:bold;">系统参数配置信息</h3>
        <hr class="layui-bg-gray">
        <div class="layui-row">
                <table class="layui-table">
                    <colgroup>
                        <col width="150">
                        <col>
                    </colgroup>
                    <tr>
                        <td style="text-align: right;">平台名称</td>
                        <td>{{COMM_SYSNAME}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">调试模式</td>
                        <td>{{(true===(bool) env('APP_DEBUG', false))?'开启':'关闭'}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">IP检测类型</td>
                        <td>{{(true===COMM_SECIP)?'双层IP检测':'单层IP检测'}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">验证码类型</td>
                        <td>{{('google'==COMM_CODETYPE)?'谷歌动态码':'图形验证码'}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">github地址</td>
                        <td> <a href="https://github.com/msg8-site/xlaravel" target="_blank" style="color:blue;">xlaravel-github</a></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">gitee地址</td>
                        <td> <a href="https://gitee.com/msg8-site/xlaravel" target="_blank" style="color:blue;">xlaravel-gitee</a></td>
                    </tr>
                </table>
        </div>
    </div>
    </div>

    @include('base/basefoot')