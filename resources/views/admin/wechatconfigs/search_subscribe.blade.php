<link href="/admin/layui/css/layui.css" rel="stylesheet">
<div class="box">
    {{--    <hr style="border-color: #fff;margin: 0;">--}}
    <ul class="nav nav-pills">
        <li><a href="index">授权信息</a></li>
        <li><a href="subscribe">外推关注回复</a></li>
        <li class="active"><a href="searchsub">直接关注回复</a></li>
        <li><a href="usertags">用户标签配置</a></li>
        <li><a href="/{{config('admin.route.prefix')}}/wechat_msg_replies">关键词回复管理</a></li>
        <li><a href="menulist">菜单设置</a></li>
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
        .col-sm-12{margin-bottom: 30px;}
        .item {/*height: 400px;*/border:8px solid #F2F2F2;border-radius: 3px;}
        .item>div:first-child{height: 40px;position: relative;border-bottom: 1px solid #ddd;}
        .item>div:first-child .title{line-height: 40px;margin-left: 20px;}
        .abs-right {position: absolute;right: 20px;}
        .layui-form-switch {height: 24px;}
        .item>div:nth-child(2){padding:  10px;}
        .item>div:nth-child(2)>div:first-child{line-height: 22px;}
        .item>div:nth-child(2) img{max-width: 100%;}
        .tip-color{color: #3b87f8;}
        .relative{position: relative;}
        .absolute{position: absolute; width: 100%; padding: 5px 10px; bottom: 0; background: rgba(0, 0, 0, .5); color: #fff; box-sizing: border-box;}
        .descinfo{padding: 20px 15px;line-height: 28px;}
        .margin-top20{margin-top: 20px;}
        .input-num{height: 19px;text-align: center;}
    </style>
    <form class="layui-form" action="" >
        <input type="hidden" name="id" value="{{$wechat['id']}}">
        <div class="box-body table-responsive no-padding" style="margin-top: 20px;">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="switch" value="1" lay-skin="switch" lay-text="ON|OFF" @if(isset($search_sub['switch']) && $search_sub['switch']==1) checked @endif >
                            </div>
                        </div>
                        <div class="title">开启/关闭</div>
                    </div>

                    <div>
                        <p>欢迎；感谢您关注《{{$account['name']}}》！</p><br>
                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">@if(isset($search_sub['title'][0])) {{$search_sub['title'][0]}} @endif</a></p><br>
{{--                        <p>【今日推荐】</p><br>--}}
                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">@if(isset($search_sub['title'][1])) {{$search_sub['title'][1]}} @endif</a></p><br>
                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">@if(isset($search_sub['title'][2])) {{$search_sub['title'][2]}} @endif</a></p><br>
                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">@if(isset($search_sub['title'][3])) {{$search_sub['title'][3]}} @endif</a></p><br>
{{--                        <p>👉<a style="color: #3b87f8;" target="_blank" href="#">@if(isset($search_sub['title'][4])) {{$search_sub['title'][4]}} @endif</a></p><br>--}}
                        <p>为方便下次阅读，请<a style="color: #3b87f8;" target="_blank" href="#">置顶公众号</a></p>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: center;padding-bottom: 50px;">
            <a href="searchsubedit?id={{$wechat['id']}}" class="btn btn-info" style="margin-top: 10px;"><i class="fa fa-edit"></i> 编辑</a>

            <button class="btn btn-primary" lay-filter="L_submit-pushconf" lay-submit id="submitdo" style="margin-top: 10px;">保存设置</button>
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
