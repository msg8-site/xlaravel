@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        setInterval(func_upheartbeat, 600000);  //每600秒(10分钟)定时触发
    });

    function func_upheartbeat() {
        $.ajax({
            type: "get",
            async: true,
            url: "ajax_heartbeat?randtime=" + Math.random()+"&uname={{session(SESS_USERNAME,'')}}&mckey={{session(SESS_MORECKEY,'')}}",
            success: function(data) {}
        });
    }

</script>
<style>
    .layui-header {
        height: 50px;
    }

    .layui-layout-admin .layui-logo {
        width: 500px;
        line-height: 50px;
        text-align: left;
        padding-left: 20px;
        font-size: 18px;
        font-weight: bold;
        color: #1E9FFF;
    }

</style>
</head>

<body>
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <div class="layui-logo">{{ COMM_SYSNAME }}</div>

        </div>

    </div>

    @include('base/basefoot')
