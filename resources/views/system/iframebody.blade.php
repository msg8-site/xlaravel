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
            <!-- <div class="layui-col-md6"> -->
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
            <!-- </div> -->
        </div>
    </div>
    </div>

    @include('base/basefoot')