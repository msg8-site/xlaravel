@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        //注意：parent 是 JS 自带的全局对象，可用于操作父页面
        var iframeindex = parent.layer.getFrameIndex(window.name); //获取窗口索引
        form.render(); //表单渲染

        if('undefined'==(typeof iframeindex)) {
            $('#closeIframe').hide();
        }
        $('#closeIframe').click(function() {
            parent.layer.close(iframeindex);
        });

        //监听提交
        form.on('submit(formtable)', function(formdata) {
            var formajaxdata = formdata.field;
            // console.log(formajaxdata)
            parent.xlayer.confirm('确认执行提交操作吗', {
                btn: ['确认', '取消']
            }, function(index) {
                parent.xlayer.close(index); //关闭弹框
                var iload = parent.xlayer.load(); //开启等待加载层
                //处理逻辑
                $.ajax({
                    type: "POST",
                    async: true,
                    url: "{{$reqarr['configurl_add_exec']??''}}",
                    data: formajaxdata,
                    success: function(res) {
                        let resc = (typeof res.c == "undefined") ? -1 : res.c;
                        let resm = (typeof res.m == "undefined") ? '' : res.m;
                        let resd = (typeof res.d == "undefined") ? {} : res.d;
                        if (200 == resc) {
                            parent.xlayer.alert(resm, {
                                end: function() {
                                    parent.layui.table.reload('tabledatalist');
                                    parent.layer.close(iframeindex);
                                }
                            });
                        } else {
                            parent.xlayer.alert(resm.replace(/\n/g, "<br>"));
                        }
                    },
                    error: function(res) {
                        try {
                            let resmessage = res.responseJSON.message;
                            if ('' != resmessage) {
                                parent.xlayer.alert('<span style="color:red;">【错误】数据请求出错，请稍后重试<br>' + resmessage + '</span>');
                            }
                        } catch (error) {
                            parent.xlayer.alert('<span style="color:red;">【错误】数据请求出错，请稍后重试</span>');
                        }
                    },
                    complete: function() {
                        parent.xlayer.close(iload); //关闭等待加载层
                    }
                });
            }, function() {
                parent.xlayer.msg('已取消');
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
            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm topfloatbar-title" disabled>标题：菜单数据添加</button>
            <button type="button" id="closeIframe" class="layui-btn layui-btn-danger layui-btn-sm">关闭当前页面</button>
            <button type="button" id="refreshPage" class="layui-btn layui-btn-normal layui-btn-sm">刷新页面</button>
            <button type="button" id="gotoTop" class="layui-btn layui-btn-sm">前往顶部</button>
            <button type="button" id="gotoBottom" class="layui-btn layui-btn-sm">前往底部</button>
            <hr class="layui-bg-gray">
        </div>
        @if ('' != ($resarr['errmsg'] ?? ''))
        <h2>{{ $resarr['errmsg'] ?? '' }}</h2>
        @else
        <h3 style="color: #1E9FFF;font-weight:bold;">菜单数据添加</h3>
        <hr class="layui-bg-gray">
        <form class="layui-form layui-form-pane" action="">
            <div class="layui-form-item">
                <label class="layui-form-label">父级菜单</label>
                <div class="layui-input-block">
                    <input type="hidden" name="fid" value="{{$resarr['fid']??''}}">
                    <input type="text" lay-verify="required" maxlength="64" autocomplete="off" placeholder="" class="layui-input disabled-color" value="{{$resarr['fidname']??''}}" disabled>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">菜单名称</label>
                <div class="layui-input-block">
                    <input type="text" name="menuname" id="menuname" lay-verify="required" maxlength="64" autocomplete="off" placeholder="菜单名称，建议控制在8个汉字以内" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">菜单路径</label>
                <div class="layui-input-block">
                    <input type="text" name="menupath" id="menupath" lay-verify="required" autocomplete="off" maxlength="256" placeholder="菜单路径，举例：index，也可以填写外链url" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">desc排序ID</label>
                <div class="layui-input-block">
                    <input type="text" name="orderbyid" id="orderbyid" lay-verify="required|number" autocomplete="off" maxlength="7" placeholder="数值越大越靠前，默认100" class="layui-input" value="100">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">备用字段1</label>
                <div class="layui-input-block">
                    <input type="text" name="backup1" id="backup1" autocomplete="off" maxlength="64" placeholder="可不填写" class="layui-input" value="">
                </div>
            </div>

            <div class="layui-form-item">
                <button type="submit" class="layui-btn layui-btn-normal" lay-submit="" lay-filter="formtable">立即提交</button>
            </div>
        </form>
        @endif
    </div>

    @include('base/basefoot')