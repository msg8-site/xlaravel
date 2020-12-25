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
            parent.layer.close(iframeindex);
        });
        $("#realtimeshowBtn").click(function() {
            refreshmarkdown();
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
                    url: "{{$reqarr['configurl_update_exec']??''}}",
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

    //刷新文档预览
    function refreshmarkdown() {
        var contentval = $("#content").val();
        $.ajax({
            type: "POST",
            async: true,
            url: "{{$reqarr['configurl_realtimeshow']??''}}",
            data: 'contentval=' + encodeURIComponent(contentval),
            success: function(res) {
                let resc = (typeof res.c == "undefined") ? -1 : res.c;
                let resm = (typeof res.m == "undefined") ? '' : res.m;
                let resd = (typeof res.d == "undefined") ? {} : res.d;
                if (200 == resc) {
                    $("#realtimepreview").html(resd);
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
                //parent.xlayer.close(iload); //关闭等待加载层
            }
        });
    }
</script>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <div class="layui-row topfloatbar">
            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm topfloatbar-title" disabled>标题：文档数据修改</button>
            <button type="button" id="closeIframe" class="layui-btn layui-btn-danger layui-btn-sm">关闭当前页面</button>
            <button type="button" id="refreshPage" class="layui-btn layui-btn-normal layui-btn-sm">刷新页面</button>
            <button type="button" id="gotoTop" class="layui-btn layui-btn-sm">前往顶部</button>
            <button type="button" id="gotoBottom" class="layui-btn layui-btn-sm">前往底部</button>
            <button type="button" id="realtimeshowBtn" class="layui-btn layui-btn-sm">预览刷新</button>
            <hr class="layui-bg-gray">
        </div>

        @if ('' != ($resarr['errmsg'] ?? ''))
        <h2>{{ $resarr['errmsg'] ?? '' }}</h2>
        @else
        <form class="layui-form layui-form-pane" action="">
            <input type="hidden" name="uplockid" value="{{$resarr['data']['uplockid'] ?? '0'}}">
            <div class="layui-form-item">
                <label class="layui-form-label">标识ID</label>
                <div class="layui-input-block">
                    <input type="text" name="id" id="id" lay-verify="required" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['data']['id'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">文档类型</label>
                <div class="layui-input-block">
                    <select name="flag" id="flag" lay-verify="required">
                        {!! $resarr['data']['option_flag'] ?? '' !!}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">文档名称</label>
                <div class="layui-input-block">
                    <input type="text" name="docname" id="docname" lay-verify="required" maxlength="64" autocomplete="off" placeholder="文档名称" class="layui-input" value="{{ $resarr['data']['docname'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">分类名称</label>
                <div class="layui-input-block">
                    <input type="text" name="typename" id="typename" lay-verify="" autocomplete="off" maxlength="64" placeholder="分类名称" class="layui-input" value="{{ $resarr['data']['typename'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">desc排序</label>
                <div class="layui-input-block">
                    <input type="text" name="orderbyid" id="orderbyid" lay-verify="" autocomplete="off" maxlength="64" placeholder="排序编号，越大越靠前" class="layui-input" value="{{ $resarr['data']['orderbyid'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">markdown文档内容</label>
                <div class="layui-input-block"">
                    <div style=" display: inline-block;width:calc(50% - 6px);">
                    <textarea name="content" id="content" class="layui-textarea" placeholder="请输入markdown格式文档内容" style="height: 700px;">{{ $resarr['data']['content'] ?? '' }}</textarea>
                </div>
                <div id="realtimepreview" class="markdown" style="display:inline-block;width:calc(50% - 6px);height:700px;overflow-y:auto;margin-left:1px;margin-top:3px;border:solid 1px #CCCCCC;">
                    <h1 style="margin:20px">点击顶部【预览刷新】按钮刷新预览页面</h1>
                </div>
            </div>
    </div>

    <div class="layui-form-item">
        <button type="submit" class="layui-btn layui-btn-normal" lay-submit="" lay-filter="formtable">立即提交</button>
    </div>
    </form>
    @endif

    </div>

    @include('base/basefoot')