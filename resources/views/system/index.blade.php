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

    #iframeheader {
        position: absolute;
        top: 0;
        left: 0;
        height: 50px;
        width: 100%;
        overflow: hidden;
        vertical-align: middle;
        border: 0px;
    }

    #iframemenu {
        position: absolute;
        top: 50px;
        left: 0;
        height: 100%;
        width: 200px;
        overflow: hidden;
        vertical-align: top;
        background-color: #D2E6FA;
        border: 0px;
    }

    #iframebody {
        position: absolute;
        left: 200px;
        top: 50px;
        height: calc(100% - 50px);
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
    <iframe name="iframeheader" id="iframeheader" src="/iframeheader"></iframe>
    <iframe name="iframemenu" id="iframemenu" src="/iframemenu"></iframe>
    <iframe name="iframebody" id="iframebody" src="/iframebody"></iframe>


    @include('base/basefoot')