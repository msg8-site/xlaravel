@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        //注意：parent 是 JS 自带的全局对象，可用于操作父页面
        var iframeindex = parent.layer.getFrameIndex(window.name); //获取窗口索引
        // form.render(); //表单渲染
        
        if('undefined'==(typeof iframeindex)) {
            $('#closeIframe').hide();
        }
        $('#closeIframe').click(function() {
            parent.layer.close(iframeindex);
        });
        $('#showhiderighttab').click(function() {
            $(".markdown-right-tab").toggle();
        });
    });
</script>
<script>
    $(function() {
        //查找h1-h6
        var headershowtab = '';
        $(".markdown :header").each(function(index, value) {
            headershowtab += '<p class="biaoti">' + value.innerText + "</p>";
        });
        $(".markdown-right-tab").append(headershowtab);
        $(".markdown-right-tab .biaoti").click(function() {
            var xuanzebiaoti = $(this).context.innerText;
            var jumptop = ($(".markdown :header:contains('" + xuanzebiaoti + "')").offset().top - 60);
            $("html,body").animate({
                scrollTop: jumptop
            }, 20);

        });
    });
</script>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <div class="layui-row topfloatbar">
            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm topfloatbar-title" disabled>标题：文档查看</button>
            <button type="button" id="closeIframe" class="layui-btn layui-btn-danger layui-btn-sm">关闭当前页面</button>
            <button type="button" id="refreshPage" class="layui-btn layui-btn-normal layui-btn-sm">刷新页面</button>
            <button type="button" class="layui-btn layui-btn-normal layui-btn-sm btncopy" data-clipboard-text="{{($resarr['data']['id']??'0')}}">复制ID:{{($resarr['data']['id']??'0')}}</button>
            <button type="button" id="gotoTop" class="layui-btn layui-btn-sm">前往顶部</button>
            <button type="button" id="gotoBottom" class="layui-btn layui-btn-sm">前往底部</button>
            <button type="button" id="showhiderighttab" class="layui-btn layui-btn-sm">隐藏/显示标题板</button>
            <hr class="layui-bg-gray">
        </div>

        @if ('' != ($resarr['errmsg'] ?? ''))
        <h2>{{ $resarr['errmsg'] ?? '' }}</h2>
        @else
        <div class="markdown-right-tab">
            <h3 style="font-weight: bold;margin-bottom:5px;">快速定位标题板</h3>
        </div>

        <div class="markdown">
            {!!$resarr['markdown']!!}
        </div>

        @endif

    </div>

    @include('base/basefoot')