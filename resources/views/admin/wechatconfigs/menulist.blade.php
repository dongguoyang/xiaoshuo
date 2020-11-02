<link href="/admin/layui/css/layui.css" rel="stylesheet">
<div class="box">
{{--    <hr style="border-color: #fff;margin: 0;">--}}
    <ul class="nav nav-pills">
        <li><a href="index">授权信息</a></li>
        <li><a href="subscribe">外推关注回复</a></li>
        <li><a href="searchsub">直接关注回复</a></li>
        <li><a href="usertags">用户标签配置</a></li>
        <li><a href="/{{config('admin.route.prefix')}}/wechat_msg_replies">关键词回复管理</a></li>
        <li class="active"><a href="menulist">菜单设置</a></li>
        <li><a href="/{{config('admin.route.prefix')}}/interactivemsg">互动消息</a></li>
        <li><a href="pushconf">智能推送</a></li>
        <li><a href="dailypush">每日推送</a></li>
        <li><a href="subscribenext">新用户第二次推送</a></li>
        <li><a href="userhpush">每日自定义推送</a></li>
        <li><a href="newmenulist">新菜单设置</a></li>
        <li><a href="centreMenuList">中部小说菜单栏设置</a></li>
        <li><a href="shorturl">生成短链接</a></li>
        <li><a href="send_message">发送模版消息</a></li>
    </ul>

    <style>
        #myTabContent table td:first-child,#myTabContent table th:first-child {padding-left: 20px;}
        .item {margin-bottom: 40px;}
        .item div{text-align: center;}
        .item .layui-form-item .layui-input-block{margin-left: auto;}
    </style>
    {{--<ul id="myTab" class="nav nav-tabs" style="margin-top: 20px;">
        <li class="active">
            <a href="#home" data-toggle="tab">
                选择默认菜单
            </a>
        </li>
        <li><a href="#ios" data-toggle="tab">
                自定义菜单
            </a>
        </li>
    </ul>--}}

    <form class="layui-form" action="" >
        <input type="hidden" name="id" value="{{$wechat['id']}}">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade in active" id="home">
                <div class="box-body table-responsive no-padding" style="margin-top: 20px;">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="item">
                            <div>
                                <img src="/admin/img/wechat_config/menulist/menu1.jpg">
                            </div>
                            <div>
                                <div class="layui-form-item" pane="">
                                    <div class="layui-input-block">
                                        <input type="radio" name="menu_list_type" value="1" title="生成菜单一" @if($wechat['menu_list']['type'] == 1) checked="" @endif >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="item">
                            <div>
                                <img src="/admin/img/wechat_config/menulist/menu2.jpg">
                            </div>
                            <div>
                                <div class="layui-form-item" pane="">
                                    <div class="layui-input-block">
                                        <input type="radio" name="menu_list_type" value="2" title="生成菜单二"  @if($wechat['menu_list']['type'] == 2) checked="" @endif>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="item">
                            <div>
                                <img src="/admin/img/wechat_config/menulist/menu3.jpg">
                            </div>
                            <div>
                                <div class="layui-form-item" pane="">
                                    <div class="layui-input-block">
                                        <input type="radio" name="menu_list_type" value="3" title="生成菜单三"  @if($wechat['menu_list']['type'] == 3) checked="" @endif >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="item">
                            <div>
                                <img src="/admin/img/wechat_config/menulist/menu4.jpg">
                            </div>
                            <div>
                                <div class="layui-form-item" pane="">
                                    <div class="layui-input-block">
                                        <input type="radio" name="menu_list_type" value="4" title="生成菜单四"  @if($wechat['menu_list']['type'] == 4) checked="" @endif >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="item">
                            <div>
                                <img src="/admin/img/wechat_config/menulist/menu5.jpg">
                            </div>
                            <div>
                                <div class="layui-form-item" pane="">
                                    <div class="layui-input-block">
                                        <input type="radio" name="menu_list_type" value="5" title="生成菜单五"  @if($wechat['menu_list']['type'] == 5) checked="" @endif >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="item">
                            <div>
                                <img src="/admin/img/wechat_config/menulist/menu6.jpg">
                            </div>
                            <div>
                                <div class="layui-form-item" pane="">
                                    <div class="layui-input-block">
                                        <input type="radio" name="menu_list_type" value="6" title="生成菜单六"  @if($wechat['menu_list']['type'] == 6) checked="" @endif >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="ios">
                <table class="table table-striped">
                    <thead>
                    <tr class="info">
                        <th>级别</th>
                        <th>名称</th>
                        <th>链接设置</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Tanmay</td>
                        <td>Bangalore</td>
                        <td>560001</td>
                    </tr>
                    <tr>
                        <td>Sachin</td>
                        <td>Mumbai</td>
                        <td>400003</td>
                    </tr>
                    <tr>
                        <td>Uma</td>
                        <td>Pune</td>
                        <td>411027</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div style="text-align: center;padding-bottom: 40px;">
            <button class="btn btn-primary" lay-filter="L_submit-menulist" lay-submit id="submitdo">保存设置</button>
        </div>
    </form>

</div>

{{--<script src="/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js"></script>--}}
<script src="/admin/layui/layui.js"></script>
<script>
    //Demo
    $("#submitdo").on('click', function () {
        $.post('#', $('.layui-form').serialize(), function(data){
            layer.msg(data.msg);
        });
        return false;
    });
    layui.use('form', function(){
        var form = layui.form;
        form.render(); // 一定要执行该语句；不然ajax加载的页面不能显示layui样式
        //监听提交
        /*form.on('submit(L_submit-menulist)', function(data){
            // layer.msg(JSON.stringify(data.field));
            // 执行ajax提交操作
            $.post('#', data.field, function(data){
                layer.msg(data.msg);
            });
            // #########
            return false;
        });*/
    });
</script>
