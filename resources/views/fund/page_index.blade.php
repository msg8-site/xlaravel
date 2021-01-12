@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        var configurl_table           = '/fund_index_tabledata';
        var configurl_add             = '/fund_add';
        var configurl_add_exec        = '/fund_add_exec';
        var configurl_index_update    = '/fund_index_update';
        var configurl_delete          = '/fund_delete';
        var configurl_mainchildupdate = '/fund_mainchildupdate';

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
                    width: 60,
                    toolbar: '#tablelinetoolbar',
                    fixed: 'left',
                    style: 'height:150px;'
                }, {
                    field: 'idfundcodename',
                    title: '基金标识',
                    fixed: 'left',
                    width: 100
                }, {
                    field: 'new_shijian',
                    title: '最近更新时间',
                    width: 95
                }, {
                    field: 'sumhas_countmoneyaverageprofit',
                    title: '总持有详细',
                    width: 100
                }, {
                    field: 'sumhas_suminout',
                    title: '累计入出',
                    width: 100
                }, {
                    field: 'zhangdiejingzhi0',
                    title: '最新0天详细',
                    width: 99,
                    style: 'background-color:#F5F5FF'
                }, {
                    field: 'zhangdiejingzhi1',
                    title: '前1天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi2',
                    title: '前2天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi3',
                    title: '前3天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi4',
                    title: '前4天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi5',
                    title: '前5天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi6',
                    title: '前6天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi10',
                    title: '前10天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi20',
                    title: '前20天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi40',
                    title: '前40天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi60',
                    title: '前60天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi80',
                    title: '前80天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi100',
                    title: '前100天详细',
                    width: 99
                }, {
                    field: 'zhangdiejingzhi120',
                    title: '前120天详细',
                    width: 99
                }, {
                    field: 'create_datetime',
                    title: '创建时间',
                    width: 145
                }, {
                    field: 'update_datetime',
                    title: '修改时间',
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
                    content: configurl_index_update + '?id=' + obj.data.id
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

        $("#ajaxresmsg").html('缓存中请稍后。。。');
        $.ajax({
            type: "GET",
            async: true,
            url: configurl_mainchildupdate,
            success: function(res) {
                $("#ajaxresmsg").html(res);
            },
            error: function(res) {
                $("#ajaxresmsg").html('缓存通讯异常，请稍后重试');
            },
            complete: function() {}
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

            $("#ajaxresmsg").html('缓存中请稍后。。。');
            $.ajax({
                type: "GET",
                async: true,
                url: configurl_mainchildupdate,
                success: function(res) {
                    $("#ajaxresmsg").html(res);
                },
                error: function(res) {
                    $("#ajaxresmsg").html('缓存通讯异常，请稍后重试');
                },
                complete: function() {}
            });
            //表单提交拦截
            return false;
        });
    });
</script>
<style>
    table tbody tr td .layui-table-cell {
        overflow: visible;
        text-overflow: inherit;
        white-space: normal;
        word-break: break-all;
    }

    table tbody tr td {
        vertical-align: top;
    }
</style>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <div class="layui-row formtoplist">
            <fieldset class="layui-elem-field layui-elem-field-search">
                <legend>条件查询</legend>
                <div class="layui-field-box">
                    <form class="layui-form layui-form-pane layui-form-search" action="">
                        <div class="layui-form-item">
                            <label class="layui-form-label">基金标识编号</label>
                            <div class="layui-input-inline">
                                <input type="text" name="fundcode" maxlength="6" placeholder="6位标识编号 [全模糊]" autocomplete="off" class="layui-input">
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
            <span id="ajaxresmsg" style="padding-left:50px;"></span>
        </script>
        <script type="text/html" id="tablelinetoolbar">
            <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="tablelinetoolbar_update" style="margin-top:12px;margin-bottom:8px;">修改</a>
            <br>
            <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="tablelinetoolbar_delete">删除</a>
        </script>
        <table id="tabledatalist" lay-filter="tablefilter"></table>
    </div>
    </div>

    @include('base/basefoot')