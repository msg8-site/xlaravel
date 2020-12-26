@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        $("#markdownmenulist").on('click', '.layui-nav-item .mainmenuname', function() {
            var nowthis = $(this);
            nowthis.parent().toggleClass('layui-nav-itemed');
        });
        $("#markdownmenulist").on('click', '.childmenuname', function() {
            var nowthis = $(this);
            var index = xlayer.load();
            $('#iframeright', parent.document).load(function() {
                xlayer.close(index)
            });
        });

        $(".icon-search-clear").click(function() {
            $("#fastsearch").val('');
            $("#fastsearch").focus();
            tosearch();
        });
        $("#fastsearch").keypress(function(e) {
            if (e.which == 13) {
                $("#fastsearch").blur();
                tosearch();
            }
        });

        $(".mainmenuname:first").trigger("click");

    });

    function tosearch() {
        searchval = $("#fastsearch").val();
        var iload = parent.xlayer.load(); //开启等待加载层
        //处理逻辑
        $.ajax({
            type: "POST",
            async: true,
            url: "/publicdoc_search",
            data: 'searchval=' + encodeURIComponent(searchval),
            success: function(res) {
                let resc = (typeof res.c == "undefined") ? -1 : res.c;
                let resm = (typeof res.m == "undefined") ? '' : res.m;
                let resd = (typeof res.d == "undefined") ? {} : res.d;
                if (200 == resc) {
                    $("#markdownmenulist").html(resd);
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
    }
</script>
</head>
<style>

</style>

<body>
    <div class="layui-layout layui-layout-admin">
        <div class="layui-side layui-bg-black">
            <div class="layui-side-scroll">
                <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
                <ul class="layui-nav layui-nav-tree layui-nav-side" lay-filter="test" style="background-color: #777777;padding-bottom:100px;">
                    <div style="position: sticky;top:0px;z-index: 999;">
                        <i class="layui-icon layui-icon-close-fill icon-search-clear"></i>
                        <input class="layui-input" type="text" name="fastsearch" id="fastsearch" autocomplete="off" placeholder="检索内容，回车提交">
                    </div>
                    <div id="markdownmenulist">
                        @foreach ($menulist as $keym => $valm)
                        <li class="layui-nav-item">
                            <!-- layui-nav-itemed -->
                            <a class="mainmenuname" href="javascript:;">{{$keym}}</a>
                            @if (count($menulist[$keym])>0)
                            <dl class="layui-nav-child">
                                @foreach ($menulist[$keym] as $keymc => $valmc)
                                <dd><a class="childmenuname" href="publicdoc_docshow?id={{ $valmc[1] }}" target="iframeright">{{ $valmc[0] }}</a></dd>
                                @endforeach
                            </dl>
                            @endif
                        </li>
                        @endforeach
                    </div>
                </ul>
            </div>
        </div>

    </div>

    @include('base/basefoot')