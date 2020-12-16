@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        var configurl_table       = '/logcodecheck_index_tabledata';
        var configurl_add         = '/logcodecheck_add';
        var configurl_add_exec    = '/logcodecheck_add_exec';
        var configurl_update      = '/logcodecheck_update';
        var configurl_update_exec = '/logcodecheck_update_exec';
        var configurl_delete      = '/logcodecheck_delete';

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
                    width: 80
                }, {
                    field: 'checkflag_show',
                    title: '验证结果',
                    width: 90
                }, {
                    field: 'describename',
                    title: '区分标识名称',
                    width: 220
                }, {
                    field: 'checkusername',
                    title: '验证用户名',
                    width: 130
                }, {
                    field: 'checkuserip',
                    title: '验证IP地址',
                    width: 120
                }, {
                    field: 'checkcode',
                    title: '验证输入的动态码',
                    width: 150
                }, {
                    field: 'create_datetime',
                    title: '创建时间',
                    width: 145
                }, {
                    field: 'moreclientkey',
                    title: '登录标识key'
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

                            <label class="layui-form-label">验证结果</label>
                            <div class="layui-input-inline">
                                <select name="checkflag" id="checkflag">
                                    <option value="">请选择</option>
                                    <option value="1">成功</option>
                                    <option value="2">失败</option>
                                </select>
                            </div>
                            <label class="layui-form-label">验证用户名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="checkusername" maxlength="64" placeholder="验证用户名 [全模糊]" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">区分标识名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="describename" maxlength="64" placeholder="区分标识名 [全模糊]" autocomplete="off" class="layui-input">
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