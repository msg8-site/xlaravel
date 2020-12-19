@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        var configurl_table       = '/user_index_tabledata';
        var configurl_add         = '/user_add';
        var configurl_add_exec    = '/user_add_exec';
        var configurl_update      = '/user_update';
        var configurl_update_exec = '/user_update_exec';
        var configurl_delete      = '/user_delete';

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
                    width: 120,
                    toolbar: '#tablelinetoolbar',
                    fixed: 'left'
                }, {
                    field: 'id',
                    title: 'ID',
                    width: 80
                }, {
                    field: 'status_show',
                    title: '状态',
                    width: 70
                }, {
                    field: 'rolename',
                    title: '角色名称ID',
                    width: 120
                }, {
                    field: 'username',
                    title: '用户名',
                    width: 150
                }, {
                    field: 'nickname',
                    title: '用户昵称',
                    width: 100
                }, {
                    field: 'usergooglekey',
                    title: '谷歌动态码',
                    width: 80
                }, {
                    field: 'moreclientflag',
                    title: '客户端限制',
                    width: 80
                }, {
                    field: 'moreclientkey',
                    title: '客户端标识',
                    width: 150
                }, {
                    field: 'useremail',
                    title: '用户邮箱',
                    width: 120
                }, {
                    field: 'userphone',
                    title: '用户手机号',
                    width: 120
                }, {
                    field: 'create_datetime',
                    title: '创建时间',
                    width: 145
                }, {
                    field: 'update_datetime',
                    title: '修改时间',
                    width: 145
                }, {
                    field: 'backup1',
                    title: '备用1',
                    width: 150
                }, {
                    field: 'backup2',
                    title: '备用2',
                    width: 150
                }, {
                    field: 'backup3',
                    title: '备用3',
                    width: 150
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
            } else if ('tablelinetoolbar_delete' == obj.event) {
                if (1 == obj.data.status) {
                    xlayer.alert('【错误】状态为开启的数据不可删除');
                } else {
                    xlayer.confirm('确认执行删除操作吗', {
                        btn: ['确认', '取消']
                    }, function(index) {
                        xlayer.close(index); //关闭弹框
                        var iload = xlayer.load(); //开启等待加载层
                        //处理逻辑
                        $.ajax({
                            type: "POST",
                            async: true,
                            url: configurl_delete,
                            data: "id=" + obj.data.id,
                            success: function(res) {
                                let resc = (typeof res.c == "undefined") ? -1 : res.c;
                                let resm = (typeof res.m == "undefined") ? '' : res.m;
                                let resd = (typeof res.d == "undefined") ? {} : res.d;
                                if (200 == resc) {
                                    xlayer.alert(resm, {
                                        end: function() {
                                            layui.table.reload('tabledatalist');
                                        }
                                    });
                                } else {
                                    xlayer.alert(resm.replace(/\n/g, "<br>"));
                                }
                            },
                            error: function(res) {
                                try {
                                    let resmessage = res.responseJSON.message;
                                    if ('' != resmessage) {
                                        xlayer.alert('<span style="color:red;">【错误】数据请求出错，请稍后重试<br>' + resmessage + '</span>');
                                    }
                                } catch (error) {
                                    xlayer.alert('<span style="color:red;">【错误】数据请求出错，请稍后重试</span>');
                                }
                            },
                            complete: function() {
                                xlayer.close(iload); //关闭等待加载层
                            }
                        });
                    }, function() {
                        xlayer.msg('已取消');
                    });
                }
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
        <div class="layui-row formtoplist">
            <fieldset class="layui-elem-field layui-elem-field-search">
                <legend>条件查询</legend>
                <div class="layui-field-box">
                    <form class="layui-form layui-form-pane layui-form-search" action="">
                        <div class="layui-form-item">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-inline">
                                <select name="status" id="status">
                                    <option value="">请选择</option>
                                    <option value="1">开启</option>
                                    <option value="2">关闭</option>
                                    <option value="4">禁用</option>
                                </select>
                            </div>
                            <label class="layui-form-label">用户名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="username" maxlength="64" placeholder="用户名 [全模糊]" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">用户昵称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="nickname" maxlength="64" placeholder="用户昵称 [全模糊]" autocomplete="off" class="layui-input">
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
            <a class="layui-btn layui-btn-sm layui-btn-normal" lay-event="tabletoolbar_add">添加</a>
        </script>
        <script type="text/html" id="tablelinetoolbar">
            <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="tablelinetoolbar_update">修改</a>
            <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="tablelinetoolbar_delete">删除</a>
        </script>
        <table id="tabledatalist" lay-filter="tablefilter"></table>
    </div>
    </div>

    @include('base/basefoot')