@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        var configurl_table = '/menu_index_tabledata';
        var configurl_add = '/menu_add';
        var configurl_add_exec = '/menu_add_exec';
        var configurl_update = '/menu_update';
        var configurl_update_exec = '/menu_update_exec';
        var configurl_delete = '/menu_delete';

        form.render(); //表单渲染
        //表格渲染
        var table = layui.table;
        table.render({
            elem: '#tabledatalist',
            height: 'full-160',
            url: configurl_table,
            // page: true,
            // limit: 20,
            // limits: [20, 50, 100],
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
                    width: 220,
                    toolbar: '#tablelinetoolbar',
                    fixed: 'left'
                }, {
                    field: 'id',
                    title: 'ID',
                    width: 80
                }, {
                    field: 'fid',
                    title: '父级ID',
                    width: 70
                }, {
                    field: 'showmenuname',
                    title: '菜单名称',
                    width: 180
                }, {
                    field: 'menupath',
                    title: '菜单路径',
                    width: 150
                }, {
                    field: 'rolenamestr',
                    title: '关联角色名',
                    width: 220
                }, {
                    field: 'orderbyid',
                    title: 'desc排序',
                    width: 80
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
                    content: configurl_add + "?id=0&configurl_add_exec=" + encodeURIComponent(configurl_add_exec)
                });
            } else {}
        });

        //行内工具监听
        table.on('tool(tablefilter)', function(obj) {
            if ('tablelinetoolbar_add' == obj.event) {
                if (0 != obj.data.fid) {
                    xlayer.alert('【错误】菜单仅支持两级，非菜单分类目录不可添加子菜单');
                } else {
                    layer.open({
                        type: 2,
                        title: '<span style="font-weight:bold;">【弹出层】数据操作窗口界面</span>',
                        shadeClose: false,
                        area: ['100%', '100%'],
                        content: configurl_add + '?id=' + obj.data.id + "&configurl_add_exec=" + encodeURIComponent(configurl_add_exec)
                    });
                }
            } else if ('tablelinetoolbar_update' == obj.event) {
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
                        <!-- <div class="layui-form-item">
                            <label class="layui-form-label">菜单名称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="menuname" maxlength="64" placeholder="菜单名称 [全模糊]" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">菜单路径</label>
                            <div class="layui-input-inline">
                                <input type="text" name="menupath" maxlength="64" placeholder="菜单路径 [全模糊]" autocomplete="off" class="layui-input">
                            </div>
                        </div> -->
                        <div class="layui-form-item">
                            <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="" lay-filter="formselect">查询数据</button>
                            <!-- <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary" lay-filter="formselectreset">重置</button> -->
                        </div>
                    </form>
                </div>
            </fieldset>


        </div>
        <script type="text/html" id="lefttoolbar">
            <a class="layui-btn layui-btn-sm layui-btn-normal" lay-event="tabletoolbar_add">添加菜单分类</a>
        </script>
        <script type="text/html" id="tablelinetoolbar">
            <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="tablelinetoolbar_add">添加子菜单</a>
            <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="tablelinetoolbar_update">修改</a>
            <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="tablelinetoolbar_delete">删除</a>
        </script>
        <table id="tabledatalist" lay-filter="tablefilter"></table>
    </div>
    </div>

    @include('base/basefoot')