@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        $(".layui-nav-item .mainmenuname").click(function() {
            var nowthis = $(this);
            nowthis.parent().toggleClass('layui-nav-itemed');
        });

        $(".childmenuname").click(function() {
            var nowthis = $(this);
            if ('退出登录' == nowthis.html() && '/logout' == nowthis.attr('href')) {
                //退出操作单独判断
                xlayer.confirm('确认要退出系统吗', {
                    btn: ['确认', '取消']
                }, function(index) {
                    xlayer.close(index); //关闭弹框
                    var iload = xlayer.load(); //开启等待加载层
                    //处理逻辑
                    $.ajax({
                        type: "POST",
                        async: true,
                        url: "/logout",
                        data: 'logout=yes',
                        success: function(res) {
                            let resc = (typeof res.c == "undefined") ? -1 : res.c;
                            let resm = (typeof res.m == "undefined") ? '' : res.m;
                            let resd = (typeof res.d == "undefined") ? {} : res.d;
                            if (200 == resc) {
                                xlayer.alert(resm, {
                                    end: function() {
                                        top.location.href = "/index"
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
                }, function() {
                    xlayer.msg('已取消');
                });
                return false;
            } else {
                var index = xlayer.load();
                $('#iframebody', parent.document).load(function() {
                    xlayer.close(index)
                });
            }

        });

    });

</script>
</head>

<body>
    <div class="layui-layout layui-layout-admin">
        <div class="layui-side layui-bg-black">
            <div class="layui-side-scroll">
                <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
                <ul class="layui-nav layui-nav-tree layui-nav-side" lay-filter="test">
                    @foreach ($menulist as $keym => $valm)
                        @if (isset($menulist[$keym]['m']))
                            <li class="layui-nav-item">
                                <!-- layui-nav-itemed -->
                                <a class="mainmenuname" href="javascript:;">{{ $menulist[$keym]['m'] }}</a>
                                @if (isset($menulist[$keym]['c']))
                                    <dl class="layui-nav-child">
                                        @foreach ($menulist[$keym]['c'] as $keymc => $valmc)
                                            <dd><a class="childmenuname" href="{{ $valmc[1] }}" target="iframebody">{{ $valmc[0] }}</a></dd>
                                        @endforeach
                                    </dl>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

    </div>

    @include('base/basefoot')
