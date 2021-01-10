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
</script>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <div class="layui-row topfloatbar">
            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm topfloatbar-title" disabled>标题：基金子表数据修改</button>
            <button type="button" id="closeIframe" class="layui-btn layui-btn-danger layui-btn-sm">关闭当前页面</button>
            <button type="button" id="refreshPage" class="layui-btn layui-btn-normal layui-btn-sm">刷新页面</button>
            <button type="button" id="gotoTop" class="layui-btn layui-btn-sm">前往顶部</button>
            <button type="button" id="gotoBottom" class="layui-btn layui-btn-sm">前往底部</button>
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
                <label class="layui-form-label">基金标识</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['maindata']['fundcode'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">基金名称</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['maindata']['fundname'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">更新时间</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['maindata']['new_shijian'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">最新净值</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['maindata']['new_jingzhi'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">持有单价</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['maindata']['sumhas_average'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">总持有份额</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['maindata']['sumhas_count'] ?? '' }}份">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">总持有金额</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['maindata']['sumhas_money'] ?? '' }}元">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">总持有盈利</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['maindata']['sumhas_profit'] ?? '' }}元">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">对应日期</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['data']['thedate'] ?? '' }}  周{{ $resarr['data']['theweek'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">对应净值</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['data']['jingzhi'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">买入金额</label>
                <div class="layui-input-block">
                    <input type="text" style="background-color:rgba(0,255,0,0.1)" name="inmoney" id="inmoney" lay-verify="" autocomplete="off" maxlength="64" placeholder="买入金额(元)" class="layui-input" value="{{ $resarr['data']['inmoney'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">卖出份额</label>
                <div class="layui-input-block">
                    <input type="text" style="background-color:rgba(255,0,0,0.1)" name="outcount" id="outcount" lay-verify="" autocomplete="off" maxlength="64" placeholder="卖出份额(份)" class="layui-input" value="{{ $resarr['data']['outcount'] ?? '' }}">
                </div>
            </div>

            <div class="layui-form-item">
                <button type="submit" class="layui-btn layui-btn-normal" lay-submit="" lay-filter="formtable">立即提交</button>
            </div>
        </form>
        @endif

    </div>

    @include('base/basefoot')