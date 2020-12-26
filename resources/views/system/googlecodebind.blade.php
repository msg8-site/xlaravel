@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        //监听提交
        form.on('submit(formtable)', function(formdata) {
            var formajaxdata = formdata.field;

            xlayer.confirm('确认执行提交操作吗', {
                btn: ['确认', '取消']
            }, function(index) {
                xlayer.close(index); //关闭弹框
                var iload = xlayer.load(); //开启等待加载层
                //处理逻辑
                $.ajax({
                    type: "POST",
                    async: true,
                    url: "googlecodebind_exec",
                    data: formajaxdata,
                    success: function(res) {
                        let resc = (typeof res.c == "undefined") ? -1 : res.c;
                        let resm = (typeof res.m == "undefined") ? '' : res.m;
                        let resd = (typeof res.d == "undefined") ? {} : res.d;
                        if (200 == resc) {
                            xlayer.alert(resm, {
                                end: function() {
                                    window.location.reload()
                                    // window.location.href="index";
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

            //表单提交拦截
            return false;
        });

    });

</script>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <h3 style="color: #1E9FFF;font-weight:bold;">用户谷歌动态码绑定</h3>
        <hr class="layui-bg-gray">
        @if ('no' === ($resarr['bindflag'] ?? 'null'))
            <form class="layui-form layui-form-pane" action="">
                <div class="layui-form-item">
                    <label class="layui-form-label">用户名</label>
                    <div class="layui-input-block">
                        <input type="text" name="username" id="username" lay-verify="required" autocomplete="off" placeholder="" class="layui-input disabled-color" disabled value="{{ session(SESS_USERNAME, '') }}">
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">绑定说明</label>
                    <div class="layui-input-block">
                        <textarea placeholder="" class="layui-textarea disabled-color" disabled style="min-height:80px;">动态码为谷歌动态码，请先下载对应软件，扫码进行绑定
安卓用户建议下载华为云APP，使用其控制台页面的MFA功能
苹果用户建议应用商店搜索下载Google Authenticator，也可以下载华为云APP</textarea>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text"">
                <label class=" layui-form-label">绑定二维码</label>
                    <div class="layui-input-block" style="padding: 2px">
                        <img src="{{ $resarr['showimgsrc'] ?? '' }}">
                        {{ $resarr['showbindmsg'] ?? '' }}
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">绑定密钥</label>
                    <div class="layui-input-block">
                        <input type="hidden" name="randbindkey" value="{{ $resarr['usergooglekey'] ?? '' }}" />
                        <textarea placeholder="" class="layui-textarea disabled-color" disabled style="min-height:60px;">如果您的软件不能使用扫码功能，可以手动输入本密钥以完成绑定
{{ $resarr['usergooglekey'] ?? '' }}</textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">动态码确认</label>
                    <div class="layui-input-block">
                        <input type="text" name="googlecode" id="googlecode" lay-verify="required" autocomplete="off" maxlength="6" placeholder="请输入6位数字动态码以确认完成绑定" class="layui-input" value="">
                    </div>
                </div>
                <div class="layui-form-item">
                    <button type="submit" class="layui-btn layui-btn-normal" lay-submit="" lay-filter="formtable">立即提交</button>
                </div>
            </form>
        @else
            <h3 style="color: grey;font-weight:bold;">谷歌动态码已经完成绑定，不可再次绑定</h3>
            <h3 style="color: grey;font-weight:bold;">如果绑定设备数据丢失，请联系管理员重置绑定</h3>
        @endif






    </div>

    @include('base/basefoot')
