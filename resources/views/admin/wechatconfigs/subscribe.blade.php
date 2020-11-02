<link href="/admin/layui/css/layui.css" rel="stylesheet">
<!-- 外推关注回复配置 -->
<div class="box">
{{--    <hr style="border-color: #fff;margin: 0;">--}}
    <ul class="nav nav-pills">
        <li><a href="index">授权信息</a></li>
        <li class="active"><a href="subscribe">外推关注回复</a></li>
        <li><a href="searchsub">直接关注回复</a></li>
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
                        <p>你好，欢迎关注 <span style="color: #3b87f8;">公众号名称</span>！</p>
                        <p><a style="color: #3b87f8;" target="_blank" href="{{$url}}">请点击我继续阅读《书名》</a></p>
                    </div>
                    <div>
                        <div class="layui-form-item" pane="">
                            <div class="layui-input-block">
                                <input type="radio" name="subscribe_msg" value="1" title="继续阅读样式一" @if($wechat['subscribe_msg'] == 1) checked="" @endif >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <p>亲爱的用户昵称，欢迎关注 <span style="color: #3b87f8;">公众号名称</span>！</p>
                        <p><a style="color: #3b87f8;" target="_blank" href="{{$url}}">点击我继续阅读刚才的小说吧！</a></p>
                    </div>
                    <div>
                        <div class="layui-form-item" pane="">
                            <div class="layui-input-block">
                                <input type="radio" name="subscribe_msg" value="2" title="继续阅读样式二"  @if($wechat['subscribe_msg'] == 2) checked="" @endif>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <p>亲爱的 用户名，欢迎您~ <a href="{{$url}}" style="color:#3b87f8; ">点击我继续阅读刚才的小说吧！</a></p>
                    </div>
                    <div>
                        <div class="layui-form-item" pane="">
                            <div class="layui-input-block">
                                <input type="radio" name="subscribe_msg" value="3" title="继续阅读样式三"  @if($wechat['subscribe_msg'] == 3) checked="" @endif >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div style="text-align: center;">
                        <div style="max-width: 330px;border: 1px solid #ddd;padding: 10px;text-align: left;margin: auto;">
                            <div style="font-weight: bold;">
                                @if(isset($wechat['subscribe_content']['title']))
                                    {{$wechat['subscribe_content']['title']}}
                                @else
                                    你好公爵大人
                                @endif
                            </div>
                            <div style="position: relative;margin-top: 5px;">
                                <div style="position: absolute;width: 225px;color: #9d9d9d;font-size: 13px;line-height: 20px;">
                                    @if(isset($wechat['subscribe_content']['desc']))
                                        {{$wechat['subscribe_content']['desc']}}
                                    @else
                                        结婚三年，她不过就是个挂名妻子，眼睁睁看着他带回来各种女人，绯闻不断。三年后...
                                    @endif
                                </div>
                                <img @if(isset($wechat['subscribe_content']['img'])) src="{{$wechat['subscribe_content']['img']}}" @else src="/img/default-avatar.png" @endif style="width: 70px;margin-left: 235px;">
                            </div>
                        </div>

                        <a href="subscribe-edit?id={{$wechat['id']}}" class="btn btn-info btn-sm" style="margin-top: 10px;"><i class="fa fa-edit"></i> 编辑</a>
                    </div>
                    <div>
                        <div class="layui-form-item" pane="">
                            <div class="layui-input-block">
                                <input type="radio" name="subscribe_msg" value="4" title="继续阅读样式四"  @if($wechat['subscribe_msg'] == 4) checked="" @endif >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <p><a style="color: #3b87f8;" target="_blank" href="{{$url}}">点击继续阅读！</a></p>
                    </div>
                    <div>
                        <div class="layui-form-item" pane="">
                            <div class="layui-input-block">
                                <input type="radio" name="subscribe_msg" value="5" title="继续阅读样式五"  @if($wechat['subscribe_msg'] == 5) checked="" @endif>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align: center;">
            <button class="btn btn-primary" lay-filter="L_submit-subscribe" lay-submit id="submitdo">保存设置</button>
        </div>
    </form>

    <div class="descinfo">
        <div>功能说明：</div>
        <div>1、公众号扫码授权后，系统会自动默认设置一种 公众号“自动回复”欢迎语，即【继续阅读样式一】。</div>
        <div>目的是为了方便用户关注后继续阅读上次看的小说章节。</div>
        <div>2、为了实现更漂亮的样式，您也可以修改选择【继续阅读样式三】。</div>
        <div>你可以在合适的地方添加 用户名，发送时会自动替换为用户微信昵称。</div>
        <div>但请注意上面的链接是带参数的，请勿更改。</div>
        <div>查看关注后发送的效果。</div>
    </div>
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
