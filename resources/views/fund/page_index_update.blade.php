@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        //注意：parent 是 JS 自带的全局对象，可用于操作父页面
        var iframeindex = parent.layer.getFrameIndex(window.name); //获取窗口索引
        form.render(); //表单渲染

        if ('undefined' == (typeof iframeindex)) {
            $('#closeIframe').hide();
        }
        $('#closeIframe').click(function() {
            console.log(iframeindex)
            parent.layer.close(iframeindex);
        });


        var configurl_table       = '/fund_index_update_tabledata';
        var configurl_update      = '/fundchild_update';
        var configurl_update_exec = '/fundchild_update_exec';

        form.render(); //表单渲染
        //表格渲染
        var table = layui.table;
        table.render({
            elem: '#tabledatalist',
            height: 'full-130',
            url: configurl_table + "?fid={{$reqarr['id']??0}}",
            page: true,
            limit: 20,
            limits: [20, 50, 100],
            loading: true,
            toolbar: '#lefttoolbar',
            defaultToolbar: ['filter', 'print', 'exports'],
            size: 'sm',
            cols: [
                [{
                    width: 80,
                    toolbar: '#tablelinetoolbar',
                    fixed: 'left'
                }, {
                    field: 'id',
                    title: 'ID',
                    width: 80
                }, {
                    field: 'fid',
                    title: 'FID',
                    width: 50
                }, {
                    field: 'thedate',
                    title: '对应日期',
                    width: 120
                }, {
                    field: 'theweek',
                    title: '周几',
                    width: 60
                }, {
                    field: 'jingzhi',
                    title: '净值(元)',
                    width: 90
                }, {
                    field: 'inmoney',
                    title: '买入金额',
                    width: 100
                }, {
                    field: 'incount',
                    title: '买入份额',
                    width: 100
                }, {
                    field: 'outmoney',
                    title: '卖出金额',
                    width: 100
                }, {
                    field: 'outcount',
                    title: '卖出份额',
                    width: 100
                }, {
                    field: 'create_datetime',
                    title: '创建时间',
                    width: 145
                }, {
                    field: 'update_datetime',
                    title: '更新时间',
                    width: 145
                }]
            ]
        });

        //顶部工具条监听
        table.on('toolbar(tablefilter)', function(obj) {
            // console.log(obj)
            if ('layevent_search' == obj.event) {
                $('.formtoplist').toggle();
            } else if ('tabletoolbar_add' == obj.event) {
                layer.open({
                    type: 2,
                    title: '<span style="font-weight:bold;">【弹出层】数据操作窗口界面</span>',
                    shadeClose: false,
                    area: ['100%', '100%'],
                    content: configurl_add + "?configurl_add_exec=" + encodeURIComponent(configurl_add_exec)
                });
            } else {}
        });

        //行内工具监听
        table.on('tool(tablefilter)', function(obj) {
            if ('tablelinetoolbar_update' == obj.event) {
                layer.open({
                    type: 2,
                    title: '<span style="font-weight:bold;">【弹出层】数据操作窗口界面</span>',
                    shadeClose: false,
                    area: ['100%', '100%'],
                    content: configurl_update + '?id=' + obj.data.id + "&configurl_update_exec=" + encodeURIComponent(configurl_update_exec)
                });
            }
        });

        //表单重置
        form.on('submit(formselectreset)', function(formdata) {
            var field = formdata.field;
            return false;
        });
        //点击查询后触发表单重载操作
        form.on('submit(formselect)', function(formdata) {
            var formajaxdata = formdata.field;
            table.reload('tabledatalist', {
                url: configurl_table,
                where: formajaxdata,
                page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            //表单提交拦截
            return false;
        });
    });
</script>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <div class="layui-row topfloatbar">
            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm topfloatbar-title" disabled>标题：基金买入卖出查看</button>
            <button type="button" id="closeIframe" class="layui-btn layui-btn-danger layui-btn-sm">关闭当前页面</button>
            <button type="button" id="refreshPage" class="layui-btn layui-btn-normal layui-btn-sm">刷新页面</button>
            <hr class="layui-bg-gray">
        </div>
        <script type="text/html" id="lefttoolbar">
        {{$resarr['maindata']['fundcode']??''}}-{{$resarr['maindata']['fundname']??''}} &nbsp;&nbsp;&nbsp;
        最新更新时间：{{$resarr['maindata']['new_shijian']??''}} &nbsp;&nbsp;&nbsp;
        最新更新净值：{{$resarr['maindata']['new_jingzhi']??''}} &nbsp;&nbsp;&nbsp;
        累计持有：{{$resarr['maindata']['sumhas_count']??''}}份 {{$resarr['maindata']['sumhas_money']??''}}元 &nbsp;&nbsp;&nbsp;
        累计盈利：{{$resarr['maindata']['sumhas_profit']??''}}元

        </script>
        <script type="text/html" id="tablelinetoolbar">
            <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="tablelinetoolbar_update">修改</a>
        </script>
        <table id="tabledatalist" lay-filter="tablefilter"></table>
    </div>
    </div>

    @include('base/basefoot')