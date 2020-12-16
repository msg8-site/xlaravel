@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        //监听提交
        form.on('submit(formtable)', function(formdata) {
            var formajaxdata = formdata.field;

            var sha256_3passwd = sha256(sha256(sha256(formajaxdata.password)));
            var randstr = Math.random().toString(36).substr(2, 9) + Math.random().toString(36).substr(3, 7);
            var checkpasswd = sha256(sha256_3passwd + randstr);

            formajaxdata.password = checkpasswd;
            formajaxdata.randstr = randstr;

            // xlayer.confirm('确认执行提交操作吗', {
            // 	btn: ['确认','取消']
            // },function(index){
            // 	xlayer.close(index);  //关闭弹框
            var iload = xlayer.load(); //开启等待加载层
            //处理逻辑
            $.ajax({
                type: "POST",
                async: true,
                url: "/login_exec",
                data: formajaxdata,
                success: function(res) {
                    let resc = (typeof res.c == "undefined") ? -1 : res.c;
                    let resm = (typeof res.m == "undefined") ? '' : res.m;
                    let resd = (typeof res.d == "undefined") ? {} : res.d;
                    if (200 == resc) {
                        xlayer.alert(resm, {
                            end: function() {
                                window.location.href = "/index"
                            }
                        });
                    } else {
                        xlayer.alert(resm.replace(/\n/g, "<br>"));
                    }
                },
                error: function() {
                    xlayer.alert('<span style="color:red;">【错误】数据请求出错，请稍后重试</span>');
                },
                complete: function() {
                    xlayer.close(iload); //关闭等待加载层
                }
            });
            // },function(){
            // 	xlayer.msg('已取消');
            // });

            //表单提交拦截
            return false;
        });

        $("#verifycode").click(function() {
            $("#verifycode").attr('src', '/imagecode?t=' + Math.random());

        });

    });

    function sha256(str) {
        return CryptoJS.SHA256(str).toString();
    }

</script>
<style>
    html,
    body {
        width: 100%;
        height: 100%;
        overflow: hidden
    }

    body {
        background: #1E9FFF;
    }

    body:after {
        content: '';
        background-repeat: no-repeat;
        background-size: cover;
        -webkit-filter: blur(3px);
        -moz-filter: blur(3px);
        -o-filter: blur(3px);
        -ms-filter: blur(3px);
        filter: blur(3px);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -1;
    }

    .layui-container {
        width: 100%;
        height: 100%;
        overflow: hidden
    }

    .admin-login-background {
        width: 360px;
        height: 300px;
        position: absolute;
        left: 50%;
        top: 40%;
        margin-left: -180px;
        margin-top: -100px;
    }

    .logo-title {
        text-align: center;
        letter-spacing: 2px;
        padding: 14px 0;
    }

    .logo-title h1 {
        color: #1E9FFF;
        font-size: 25px;
        font-weight: bold;
    }

    .login-form {
        background-color: #fff;
        border: 1px solid #fff;
        border-radius: 3px;
        padding: 14px 20px;
        box-shadow: 0 0 8px #eeeeee;
    }

    .login-form .layui-form-item {
        position: relative;
    }

    .login-form .layui-form-item label {
        position: absolute;
        left: 1px;
        top: 1px;
        width: 38px;
        line-height: 36px;
        text-align: center;
        color: #d2d2d2;
    }

    .login-form .layui-form-item input {
        padding-left: 36px;
    }

    .captcha {
        width: 60%;
        display: inline-block;
    }

    .captcha-img {
        display: inline-block;
        width: 34%;
    }

    .captcha-img img {
        height: 34px;
        border: 1px solid #e6e6e6;
        height: 36px;
        width: 100%;
    }

</style>
</head>

<body>
    <div class="layui-container">
        <div class="admin-login-background">
            <div class="layui-form login-form">
                <form class="layui-form" action="">
                    <div class="layui-form-item logo-title">
                        <h1>{{ COMM_SYSNAME }}登录</h1>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-icon layui-icon-username"></label>
                        <input type="text" name="username" id="username" lay-verify="required" placeholder="请输入用户名" autocomplete="off" maxlength="64" class="layui-input" value="">
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-icon layui-icon-password"></label>
                        <input type="password" name="password" id="password" lay-verify="required" placeholder="请输入密码" autocomplete="off" maxlength="64" class="layui-input" value="">
                    </div>
                    @if ('google' == COMM_CODETYPE)
                        <div class="layui-form-item">
                            <label class="layui-icon layui-icon-vercode"></label>
                            <input type="text" name="checkcode" id="checkcode" lay-verify="required" placeholder="请输入谷歌动态码" autocomplete="off" maxlength="6" class="layui-input" value="">
                        </div>
                    @else
                        <div class="layui-form-item">
                            <label class="layui-icon layui-icon-vercode"></label>
                            <input type="text" name="checkcode" id="checkcode" lay-verify="required" placeholder="图形验证码" autocomplete="off" maxlength="4" class="layui-input verification captcha" value="">
                            <div class="captcha-img">
                                <img id="verifycode" src="{{ url('/imagecode') }}">
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item">
                        <button class="layui-btn layui-btn layui-btn-normal layui-btn-fluid" lay-submit="" lay-filter="formtable">登 录</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('base/basefoot')
