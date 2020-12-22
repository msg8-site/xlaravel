@include('base/basehead')
<script type="text/javascript">
    $("document").ready(function() {
        //注意：parent 是 JS 自带的全局对象，可用于操作父页面
        var iframeindex = parent.layer.getFrameIndex(window.name); //获取窗口索引
        form.render(); //表单渲染

        if('undefined'==(typeof iframeindex)) {
            $('#closeIframe').hide();
        }
        $('#closeIframe').click(function() {
            parent.layer.close(iframeindex);
        });

        //监听提交
        form.on('submit(formtable)', function(formdata) {
            var arr_box = [];
            $('input[type=checkbox]:checked').each(function() {
                arr_box.push($(this).val());
            });
            //数组
            console.log(arr_box);

            var formajaxdata = formdata.field;
            console.log(formajaxdata)
            parent.xlayer.confirm('确认执行提交操作吗', {
                btn: ['确认', '取消']
            }, function(index) {
                parent.xlayer.close(index); //关闭弹框
                var iload = parent.xlayer.load(); //开启等待加载层
                //处理逻辑
                $.ajax({
                    type: "POST",
                    async: true,
                    url: "{{$reqarr['configurl_add_exec']??''}}",
                    data: formajaxdata,
                    success: function(res) {
                        let resc = (typeof res.c == "undefined") ? -1 : res.c;
                        let resm = (typeof res.m == "undefined") ? '' : res.m;
                        let resd = (typeof res.d == "undefined") ? {} : res.d;
                        if (200 == resc) {
                            parent.xlayer.alert(resm, {
                                end: function() {
                                    parent.layui.table.reload('tabledatalist');
                                    parent.layer.close(iframeindex);
                                }
                            });
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
            }, function() {
                parent.xlayer.msg('已取消');
            });
            //表单提交拦截
            return false;
        });
        form.on('checkbox()', function(data) {
            var pc = data.elem.classList //获取选中的checkbox的class属性
            /* checkbox处于选中状态  */
            if (data.elem.checked == true) { //并且当前checkbox为选中状态
                /*如果是parent节点 */
                if ('parent' == pc) { //如果当前选中的checkbox class里面有parent
                    //获取当前checkbox的兄弟节点的孩子们是 input[type='checkbox']的元素
                    var c = $(data.elem).siblings().children("input[type='checkbox']");
                    c.each(function() { //遍历他们的孩子们
                        var e = $(this); //添加layui的选中的样式   控制台看元素
                        e.prop('checked', true);
                        e.next().addClass("layui-form-checked");
                    });
                } else if ('child' == pc) {
                    /*如果不是parent*/
                    //选中子级选中父级
                    $(data.elem).parent().prev().prev().prop('checked', true);
                    $(data.elem).parent().prev().addClass("layui-form-checked");
                }
            } else {
                /*checkbox处于 false状态*/
                //父级没有选中 取消所有的子级选中
                if ('parent' == pc) {
                    /*判断当前取消的是父级*/
                    var c = $(data.elem).siblings().children("input[type='checkbox']");
                    c.each(function() {
                        var e = $(this);
                        e.prop('checked', false);
                        e.next().removeClass("layui-form-checked")
                    });
                } else if ('child' == pc) {
                    /*不是父级*/
                    var c = $(data.elem).siblings("div");
                    var count = 0;
                    c.each(function() { //遍历他们的孩子们
                        //如果有一个==3那么久说明是处于选中状态
                        var is = $(this).get(0).classList;
                        if (is.length == 3) {
                            count++;
                        }
                    });
                    // console.log(count)
                    //如果大于0说明还有子级处于选中状态
                    if (count > 0) {

                    } else {
                        /*如果不大于那么就说明没有子级处于选中状态那么就移除父级的选中状态*/
                        $(data.elem).parent().prev().prev().prop('checked', false);
                        $(data.elem).parent().prev().removeClass("layui-form-checked");
                    }
                }
            }
            form.render(); //重新渲染
        });
    });
</script>
<style>
    .layui-form-item .layui-form-checkbox[lay-skin="primary"] {
        margin-top: 4px;
    }

    .layui-form-pane .layui-table .layui-form-checkbox {
        margin: 0px 0px 0px 5px;
    }

    .layui-form-pane .layui-form-checkbox {
        margin: 0px 0px 0px 5px;
    }
</style>
</head>

<body class="mainbody">
    <div class="layui-container-tab">
        <div class="layui-row topfloatbar">
            <button type="button" class="layui-btn layui-btn-primary layui-btn-sm topfloatbar-title" disabled>标题：角色数据添加</button>
            <button type="button" id="closeIframe" class="layui-btn layui-btn-danger layui-btn-sm">关闭当前页面</button>
            <button type="button" id="refreshPage" class="layui-btn layui-btn-normal layui-btn-sm">刷新页面</button>
            <button type="button" id="gotoTop" class="layui-btn layui-btn-sm">前往顶部</button>
            <button type="button" id="gotoBottom" class="layui-btn layui-btn-sm">前往底部</button>
            <hr class="layui-bg-gray">
        </div>

        <form class="layui-form layui-form-pane" action="">
            <div class="layui-form-item">
                <label class="layui-form-label">角色名称</label>
                <div class="layui-input-block">
                    <input type="text" name="rolename" id="rolename" lay-verify="required" maxlength="64" autocomplete="off" placeholder="角色的标识名称" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">角色关联菜单列表</label>
                <div class="layui-input-block">
                    @foreach ($resarr['menulist'] as $keys => $vals)
                    <ul class="layui-input-block" style="margin-top: 10px ;">
                        <li>
                            @if (isset($resarr['menulist'][$keys]['m']))
                            <input type="checkbox" class="parent" name="selectmenu[]" value="{{$resarr['menulist'][$keys]['m']['id']??''}}" title="{{$resarr['menulist'][$keys]['m']['menuname']??''}}" lay-skin="primary">
                            @endif
                            @if (isset($resarr['menulist'][$keys]['c']))
                            <ul>
                                @foreach ($resarr['menulist'][$keys]['c'] as $keyss => $valss)
                                <input type="checkbox" class="child" name="selectmenu[]" value="{{$valss['id']??''}}" title="|------- {{$valss['menuname']??''}}" lay-skin="primary"><br>
                                @endforeach
                            </ul>
                            @endif
                        </li>
                    </ul>
                    @endforeach
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">角色关联节点权限</label>
                <div class="layui-input-block">
                    <table class="layui-table" lay-size="sm">
                        <colgroup>
                            <col width="10">
                            <col width="200">
                            <col width="260">
                            <col width="200">
                            <col width="320">
                            <col>
                        </colgroup>
                        @foreach ($resarr['nodelist'] as $keyn => $valn)
                        @if('jump'==$valn??'')
                        <tr style="height: 18px;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        @else
                        <tr>
                            <td><input type="checkbox" name="selectnode[]" value="{{$valn->id??''}}" lay-skin="primary" {{(''!=($valn->tsmsg??''))?'checked':''}}></td>
                            <td>{{$valn->nodename??''}}</td>
                            <td>{{$valn->routepath??''}}</td>
                            <td>{{$valn->classname??''}}</td>
                            <td>{{$valn->functionname??''}}</td>
                            <td>{{$valn->tsmsg??''}}</td>
                        </tr>
                        @endif
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">备用字段1</label>
                <div class="layui-input-block">
                    <input type="text" name="backup1" id="backup1" autocomplete="off" maxlength="64" placeholder="可不填写" class="layui-input" value="">
                </div>
            </div>

            <div class="layui-form-item">
                <button type="submit" class="layui-btn layui-btn-normal" lay-submit="" lay-filter="formtable">立即提交</button>
            </div>
        </form>

    </div>

    @include('base/basefoot')