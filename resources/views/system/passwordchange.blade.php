@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        //监听提交
        form.on('submit(formtable)', function(formdata) {
            var formajaxdata = formdata.field;
            var oldpasswd = $.trim(formajaxdata.oldpasswd);
            var newpasswd = $.trim(formajaxdata.newpasswd);
            var newpasswd2 = $.trim(formajaxdata.newpasswd2);

            if ('' == oldpasswd) {
                xlayer.alert('【错误】原密码不能为空');
                return false;
            }
            if (newpasswd.length < 8) {
                xlayer.alert('【错误】新密码长度必须大于等于8位');
                return false;
            }
            if (newpasswd2.length < 8) {
                xlayer.alert('【错误】重复新密码长度必须大于等于8位');
                return false;
            }
            if (newpasswd != newpasswd2) {
                xlayer.alert('【错误】两次密码不一致');
                return false;
            }

            xlayer.confirm('确认执行提交操作吗', {
                btn: ['确认', '取消']
            }, function(index) {
                xlayer.close(index); //关闭弹框
                var iload = xlayer.load(); //开启等待加载层
                //处理逻辑
                formajaxdata.oldpasswd = sha256(sha256(sha256(oldpasswd)));
                formajaxdata.newpasswd = sha256(sha256(sha256(newpasswd)));
                formajaxdata.newpasswd2 = sha256(sha256(sha256(newpasswd)));

                $.ajax({
                    type: "POST",
                    async: true,
                    url: "passwordchange_exec",
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

    function sha256(str) {
        return CryptoJS.SHA256(str).toString();
    }

</script>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <h3 style="color: #1E9FFF;font-weight:bold;">用户密码修改</h3>
        <hr class="layui-bg-gray">
        <form class="layui-form layui-form-pane" action="">
            <div class="layui-form-item">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-block">
                    <input type="text" name="username" id="username" lay-verify="required" autocomplete="off" maxlength="64" placeholder="" class="layui-input disabled-color" disabled value="{{ session(SESS_USERNAME, '') }}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">原密码</label>
                <div class="layui-input-block">
                    <input type="password" name="oldpasswd" id="oldpasswd" lay-verify="required" autocomplete="off" maxlength="64" placeholder="请输入原密码" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">新密码</label>
                <div class="layui-input-block">
                    <input type="password" name="newpasswd" id="newpasswd" lay-verify="required" autocomplete="off" maxlength="64" placeholder="请输入新密码" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">确认新密码</label>
                <div class="layui-input-block">
                    <input type="password" name="newpasswd2" id="newpasswd2" lay-verify="required" autocomplete="off" maxlength="64" placeholder="请再次输入新密码" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <button type="submit" class="layui-btn layui-btn-normal" lay-submit="" lay-filter="formtable">立即提交</button>
            </div>
        </form>


    </div>

    @include('base/basefoot')
