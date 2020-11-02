<link href="/admin/layui/css/layui.css" rel="stylesheet">
<div class="box">
    {{--    <hr style="border-color: #fff;margin: 0;">--}}
    <ul class="nav nav-pills">
        <li><a href="index">授权信息</a></li>
        <li><a href="subscribe">外推关注回复</a></li>
        <li><a href="searchsub">直接关注回复</a></li>
        <li><a href="usertags">用户标签配置</a></li>
        <li><a href="/{{config('admin.route.prefix')}}/wechat_msg_replies">关键词回复管理</a></li>
        <li><a href="menulist">菜单设置</a></li>
        <li><a href="/{{config('admin.route.prefix')}}/interactivemsg">互动消息</a></li>
        <li><a href="pushconf">智能推送</a></li>
        <li><a href="dailypush">每日推送</a></li>
        <li class="active"><a href="subscribenext">新用户第二次推送</a></li>
        <li><a href="userhpush">每日自定义推送</a></li>
        <li><a href="newmenulist">新菜单设置</a></li>
        <li><a href="centreMenuList">中部小说菜单栏设置</a></li>
        <li><a href="shorturl">生成短链接</a></li>
        <li><a href="send_message">发送模版消息</a></li>
    </ul>

    <style>
        .col-sm-12{margin-bottom: 30px;}
        .item {-webkit-tap-highlight-color: rgba(0,0,0,0);box-shadow: 0px 0px 10px #e8ebed;border-radius: 2px;box-sizing: border-box;
            border: 1px solid #e8ebed;padding: 20px 10px;}
        .item>div:first-child{height: 300px;}
        .item>div:nth-child(2){height: 40px;border-top: 1px solid #ddd;text-align: center;}
        .item>div:nth-child(2) .layui-input-block {margin: 0;}

        .descinfo{padding: 20px 15px;line-height: 28px;}
    </style>
    <form class="layui-form" action="" >
        <input type="hidden" name="id" value="{{$wechat['id']}}">
        <div class="box-body table-responsive no-padding" style="margin-top: 20px;">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <p>恭喜您；获得以下图书优先阅读权！</p><br>
                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">点我继续上次阅读</a></p><br>
                        <p>【今日推荐】</p><br>
                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">{{$next_msg['title'][0]}}</a></p><br>
                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">{{$next_msg['title'][1]}}</a></p><br>
                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">{{$next_msg['title'][2]}}</a></p><br>
                        <p>{{$next_msg['bottom'][0]}}，<a style="color: #3b87f8;" target="_blank" href="#">{{$next_msg['bottom'][2]}}</a></p>
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align: center;padding-bottom: 50px;">
            <a href="subscribenextedit?id={{$wechat['id']}}" class="btn btn-info" style="margin-top: 10px;"><i class="fa fa-edit"></i> 编辑</a>
        </div>
    </form>

</div>

{{--<script src="/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js"></script>--}}
<script src="/admin/layui/layui.js"></script>
<script>
    $("#submitdo").on('click', function () {
        $.post('#', $('.layui-form').serialize(), function(data){
            layer.msg(data.msg);
        });
        return false;
    });
    //Demo
    layui.use('form', function(){
        var form = layui.form;
        form.render(); // 一定要执行该语句；不然ajax加载的页面不能显示layui样式

        //监听提交
        /*form.on('submit(L_submit-subscribe)', function(data){
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
