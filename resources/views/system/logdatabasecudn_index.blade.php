@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        var configurl_table       = '/logdatabasecudn_index_tabledata';
        var configurl_add         = '/logdatabasecudn_add';
        var configurl_add_exec    = '/logdatabasecudn_add_exec';
        var configurl_update      = '/logdatabasecudn_update';
        var configurl_update_exec = '/logdatabasecudn_update_exec';
        var configurl_delete      = '/logdatabasecudn_delete';

        var laydate = layui.laydate;
        form.render(); //表单渲染
        //表格渲染
        var table = layui.table;
        table.render({
            elem: '#tabledatalist',
            height: 'full-160',
            url: configurl_table,
            page: true,
            limit: 20,
            limits: [20, 50, 100],
            loading: true,
            toolbar: '#lefttoolbar',
            defaultToolbar: ['filter', 'print', 'exports', {
                title: '条件查询框 隐藏/显示',
                layEvent: 'layevent_search',
                icon: 'layui-icon-search'
            }],
            size: 'sm',
            cols: [
                [{
                    field: 'id',
                    title: 'ID',
                    fixed: 'left',
                    width: 80
                }, {
                    field: 'type_show',
                    title: '类型',
                    fixed: 'left',
                    width: 70
                }, {
                    field: 'username',
                    title: '用户名',
                    width: 130
                }, {
                    field: 'userip',
                    title: 'IP地址',
                    width: 120
                }, {
                    field: 'moreclientkey',
                    title: '客户端标识',
                    width: 150
                }, {
                    field: 'cudnclassname',
                    title: '操作对应类名',
                    width: 145
                }, {
                    field: 'cudnfunctionname',
                    title: '操作对应函数名',
                    width: 220
                }, {
                    field: 'tablename',
                    title: '操作数据表名称',
                    width: 145
                }, {
                    field: 'tableid',
                    title: '数据表ID',
                    width: 80
                }, {
                    field: 'create_datetime',
                    title: '创建时间',
                    width: 145
                }, {
                    field: 'cudndata',
                    title: '操作数据记录',
                    width: 800
                }]
            ]
        });

        //顶部工具条监听
        table.on('toolbar(tablefilter)', function(obj) {
            // console.log(obj)
            if ('layevent_search' == obj.event) {
                $('.formtoplist').toggle();
            } else {}
        });

        //行内工具监听
        table.on('tool(tablefilter)', function(obj) {});

        // //表单重置
        form.on('click(formselectreset)', function(formdata) {
            var field = formdata.field;
            return false;
        });
        //点击查询后触发表单重载操作
        form.on('submit(formselect)', function(formdata) {
            if ('' == $('#startdatetime').val()) {
                laydate.render({
                    elem: '#startdatetime',
                    type: 'datetime',
                    value: "{{$reqarr['startdatetime']??''}}"
                });
            }
            if ('' == $('#enddatetime').val()) {
                laydate.render({
                    elem: '#enddatetime',
                    type: 'datetime',
                    value: "{{$reqarr['enddatetime']??''}}"
                });
            }

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

        laydate.render({
            elem: '#startdatetime',
            type: 'datetime',
            value: "{{$reqarr['startdatetime']??''}}"
        });
        laydate.render({
            elem: '#enddatetime',
            type: 'datetime',
            value: "{{$reqarr['enddatetime']??''}}"
        });
    });
</script>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <div class="layui-row formtoplist">
            <fieldset class="layui-elem-field layui-elem-field-search">
                <legend>条件查询</legend>
                <div class="layui-field-box">
                    <form class="layui-form layui-form-pane layui-form-search" action="">
                        <div class="layui-form-item">
                            <label class="layui-form-label">开始时间</label>
                            <div class="layui-input-inline">
                                <input type="text" name="startdatetime" id="startdatetime" maxlength="64" placeholder="开始时间" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">结束时间</label>
                            <div class="layui-input-inline">
                                <input type="text" name="enddatetime" id="enddatetime" maxlength="64" placeholder="结束时间" autocomplete="off" class="layui-input">
                            </div>

                            <label class="layui-form-label">类型</label>
                            <div class="layui-input-inline">
                                <select name="type" id="type">
                                    <option value="">请选择</option>
                                    <option value="c">添加</option>
                                    <option value="u">修改</option>
                                    <option value="d">删除</option>
                                    <option value="n">其他</option>
                                </select>
                            </div>
                            <label class="layui-form-label">操作用户名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="username" maxlength="64" placeholder="操作用户名 [全模糊]" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">操作IP地址</label>
                            <div class="layui-input-inline">
                                <input type="text" name="userip" maxlength="64" placeholder="操作IP地址 [全模糊]" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">客户端标识</label>
                            <div class="layui-input-inline">
                                <input type="text" name="moreclientkey" maxlength="64" placeholder="客户端登陆后标识" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">操作类名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="cudnclassname" maxlength="64" placeholder="操作类名" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">操作函数名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="cudnfunctionname" maxlength="128" placeholder="操作函数名" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">数据表名称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="tablename" maxlength="128" placeholder="数据表名称" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">数据表ID</label>
                            <div class="layui-input-inline">
                                <input type="text" name="tableid" maxlength="16" placeholder="数据表主键ID" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="" lay-filter="formselect">查询数据</button>
                            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary" lay-filter="formselectreset">重置</button>
                        </div>
                    </form>
                </div>
            </fieldset>


        </div>
        <script type="text/html" id="lefttoolbar">
        </script>
        <script type="text/html" id="tablelinetoolbar">
        </script>
        <table id="tabledatalist" lay-filter="tablefilter"></table>
    </div>
    </div>

    @include('base/basefoot')