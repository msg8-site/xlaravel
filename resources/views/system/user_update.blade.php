@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        //注意：parent 是 JS 自带的全局对象，可用于操作父页面
        var iframeindex = parent.layer.getFrameIndex(window.name); //获取窗口索引
        form.render(); //表单渲染

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
            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm topfloatbar-title" disabled>标题：用户数据修改</button>
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
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-block">
                    <input type="text" name="username" id="username" lay-verify="required" maxlength="64" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ $resarr['data']['username'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">角色关联</label>
                <div class="layui-input-block">
                    <select name="role" id="role" lay-verify="required">
                        {!! $resarr['data']['option_role'] ?? '' !!}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-block">
                    <select name="status" id="status" lay-verify="required">
                        {!! $resarr['data']['option_status'] ?? '' !!}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">重置密码</label>
                <div class="layui-input-block">
                    <input type="checkbox" name="resetpasswd" title="重置密码">
                    <span class="layui-word-aux">如果想要重置用户密码，请勾选此项，提交后成功后会返回随机重置的密码</span>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">重置动态码</label>
                <div class="layui-input-block">
                    <input type="checkbox" name="resetusergooglekey" title="重置谷歌动态码">
                    <span class="layui-word-aux">重置谷歌动态码后，用户可以在后台操作绑定新的动态码</span>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">用户昵称</label>
                <div class="layui-input-block">
                    <input type="text" name="nickname" id="nickname" lay-verify="required" autocomplete="off" maxlength="64" placeholder="" class="layui-input" value="{{ $resarr['data']['nickname'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">终端限制</label>
                <div class="layui-input-block">
                    <select name="moreclientflag" id="moreclientflag" lay-verify="required">
                        {!! $resarr['data']['option_moreclientflag'] ?? '' !!}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">用户邮箱</label>
                <div class="layui-input-block">
                    <input type="text" name="useremail" id="useremail" autocomplete="off" maxlength="128" placeholder="" class="layui-input" value="{{ $resarr['data']['useremail'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">用户手机</label>
                <div class="layui-input-block">
                    <input type="text" name="userphone" id="userphone" autocomplete="off" maxlength="32" placeholder="" class="layui-input" value="{{ $resarr['data']['userphone'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">备用字段1</label>
                <div class="layui-input-block">
                    <input type="text" name="backup1" id="backup1" autocomplete="off" maxlength="64" placeholder="" class="layui-input" value="{{ $resarr['data']['backup1'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">备用字段2</label>
                <div class="layui-input-block">
                    <input type="text" name="backup2" id="backup2" autocomplete="off" maxlength="64" placeholder="" class="layui-input" value="{{ $resarr['data']['backup2'] ?? '' }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">备用字段3</label>
                <div class="layui-input-block">
                    <input type="text" name="backup3" id="backup3" autocomplete="off" maxlength="64" placeholder="" class="layui-input" value="{{ $resarr['data']['backup3'] ?? '' }}">
                </div>
            </div>

            <div class="layui-form-item">
                <button type="submit" class="layui-btn layui-btn-normal" lay-submit="" lay-filter="formtable">立即提交</button>
            </div>
        </form>
        @endif

    </div>

    @include('base/basefoot')