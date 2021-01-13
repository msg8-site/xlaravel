@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {

    });
</script>
<style>
    body {
        margin: 0;
        padding: 0;
        border: 0;
        overflow-y: hidden;
        height: 100%;
        min-width: 1000px;
        max-height: 100%;
    }

    #iframeleft {
        position: absolute;
        top: 0px;
        left: 0;
        height: 100%;
        width: 200px;
        overflow: hidden;
        vertical-align: top;
        background-color: #D2E6FA;
        border: 0px;
    }

    #iframeright {
        position: absolute;
        left: 200px;
        top: 0px;
        height: 100%;
        width: calc(100% - 200px);
        min-width: 800px;
        right: 0;
        bottom: 0;
        background: #fff;
        border: 0px;
    }
</style>
</head>

<body>
    <iframe name="iframeleft" id="iframeleft" src="publicdoc_leftmenu"></iframe>
    <iframe name="iframeright" id="iframeright" src="publicdoc_rightbody"></iframe>

    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?2186da26c71fce654d35955cccf5ad67";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>

    @include('base/basefoot')