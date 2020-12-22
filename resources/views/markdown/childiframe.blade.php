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
    <iframe name="iframeleft" id="iframeleft" src="/markdown_leftmenu"></iframe>
    <iframe name="iframeright" id="iframeright" src="/markdown_rightbody"></iframe>


    @include('base/basefoot')