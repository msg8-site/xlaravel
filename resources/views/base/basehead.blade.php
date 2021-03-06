<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ (''==($title??''))?COMM_SYSNAME:($title??'') }}</title>
    <link rel="stylesheet" href="https://cdn.staticfile.org/layui/2.5.7/css/layui.min.css">
    <link rel="stylesheet" href="/css/main.css">
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="https://cdn.staticfile.org/layui/2.5.7/layui.all.min.js"></script>
    <script src="https://cdn.staticfile.org/crypto-js/3.1.9/crypto-js.min.js"></script>
    <script src="https://cdn.staticfile.org/clipboard.js/2.0.6/clipboard.min.js"></script>
    <script>
        var xlayui = layui;
        if (self.frameElement) {
            xlayui = window.parent.layui;
        }
        var xlayer = xlayui.layer;

        var $ = layui.jquery;
        var form = layui.form;

        window.onload = function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#refreshPage').click(function() {
                layer.load();
                window.location.reload();
            });
            $('#gotoTop').click(function() {
                $("html,body").animate({
                    scrollTop: 0
                }, 20);
            });
            $('#gotoBottom').click(function() {
                $('html,body').animate({
                    scrollTop: $(document).height()
                }, 20);
            });
            //复制到剪切板操作
            var clipboard = new ClipboardJS('.btncopy');
            clipboard.on('success', function(e) {
                xlayer.msg('复制成功<br>' + e.text);
                e.clearSelection();
            });
            clipboard.on('error', function(e) {
                xlayer.msg('<span style="color:red;">复制失败，请手动复制</span>');
            });
        }
    </script>