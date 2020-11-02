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
        <li class="active"><a href="dailypush">每日推送</a></li>
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
                                <input type="checkbox" name="daily_push[h6]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['daily_push']['h6']) checked @endif >
                            </div>
                        </div>
                        <div class="title">早上6点推送</div>
                    </div>
                    <div>
                        <div>开启后系统将向用户推送当日阅读过书籍 <span class="tip-color"> 推送时间：6:00</span></div>
                        <div class="relative margin-top20">
                            <div style="text-align: center;">
                                <div style="max-width: 330px;border: 1px solid #ddd;padding: 10px;text-align: left;margin: auto;">
                                    <div style="font-weight: bold;">
                                            恭喜亲爱的@{nickname}，获得随机【签到卡】&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    </div>
                                    <div style="position: relative;margin-top: 5px;">
                                        <div style="position: absolute;width: 225px;color: #9d9d9d;font-size: 13px;line-height: 20px;">
                                                👆👆👆点击此处查看
                                        </div>
                                        <img src="/img/default-avatar.png" style="width: 70px;margin-left: 235px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="daily_push[h12]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['daily_push']['h12']) checked @endif >
                            </div>
                        </div>
                        <div class="title">中午12点推送</div>
                    </div>
                    <div>
                        <div>开启后系统将向用户推送阅读历史记录 <span class="tip-color"> 推送时间：12:00</span></div>
                        <div class="relative margin-top20">
                            <div style="text-align: center;">
                                <div style="max-width: 330px;border: 1px solid #ddd;padding: 10px;text-align: left;margin: auto;">
                                    <div style="font-weight: bold;">
                                            @亲爱的{nickname}，点击这里，未读新消息不错过&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    </div>
                                    <div style="position: relative;margin-top: 5px;">
                                        <div style="position: absolute;width: 225px;color: #9d9d9d;font-size: 13px;line-height: 20px;">
                                                👆👆👆点击此处查看
                                        </div>
                                        <img src="/img/default-avatar.png" style="width: 70px;margin-left: 235px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="daily_push[h18]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['daily_push']['h18']) checked @endif >
                            </div>
                        </div>
                        <div class="title">下午18点推送</div>
                    </div>
                    <div>
                        <div>开启后系统将向用户推送随机一本小说 <span class="tip-color"> 推送时间：18:00</span></div>
                        <div class="relative margin-top20">
                            <div style="text-align: center;">
                                <div style="max-width: 330px;border: 1px solid #ddd;padding: 10px;text-align: left;margin: auto;">
                                    <div style="font-weight: bold;">
                                            标题库随机标题&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    </div>
                                    <div style="position: relative;margin-top: 5px;">
                                        <div style="position: absolute;width: 225px;color: #9d9d9d;font-size: 13px;line-height: 20px;">
                                                👆👆👆点击此处查看
                                        </div>
                                        <img src="/img/default-avatar.png" style="width: 70px;margin-left: 235px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="daily_push[h21]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['daily_push']['h21']) checked @endif >
                            </div>
                        </div>
                        <div class="title">晚上21点推送</div>
                    </div>
                    <div>
                        <div>开启后系统将向用户推送充值优惠礼包 <span class="tip-color"> 推送时间：21:00</span></div>
                        <div class="relative margin-top20">
                            <div style="text-align: center;">
                                <div style="max-width: 330px;border: 1px solid #ddd;padding: 10px;text-align: left;margin: auto;">
                                    <div style="font-weight: bold;">
                                            您的新用户专享礼包已送达
                                            <br><br>
                                            {{str_replace('<br>', '，', $moneyBtn['title'])}}，只有一次机会哦！
                                            <br><br>
                                            过期失效，不要错过！
                                            <br><br>
                                            <span class="tip-color">点击立即领取>></span>
                                            <br><br>
                                            为方便下次阅读，请<a class="tip-color" href='{$host}/img/wechat.totop.png'>置顶公众号</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="daily_push[h23]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['daily_push']['h23']) checked @endif >
                            </div>
                        </div>
                        <div class="title">晚上23点推送</div>
                    </div>
                    <div>
                        <div>开启后系统将向用户推送一本阅读量最高的小说 <span class="tip-color"> 推送时间：23:00</span></div>
                        <div class="relative margin-top20">
                            <div style="text-align: center;">
                                <div style="max-width: 330px;border: 1px solid #ddd;padding: 10px;text-align: left;margin: auto;">
                                    <div style="font-weight: bold;">
                                            标题库随机标题
                                    </div>
                                    <div style="position: relative;margin-top: 5px;">
                                        <div style="position: absolute;width: 225px;color: #9d9d9d;font-size: 13px;line-height: 20px;">
                                                👆👆👆点击此处查看
                                        </div>
                                        <img src="/img/default-avatar.png" style="width: 70px;margin-left: 235px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div style="text-align: center;clear: both;padding-bottom: 50px;">
            <button class="btn btn-primary" lay-filter="L_submit-pushconf" lay-submit id="submitdo">保存设置</button>
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
        /*form.on('submit(L_submit-pushconf)', function(data){
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
