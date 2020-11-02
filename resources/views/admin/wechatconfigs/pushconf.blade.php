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
        <li class="active"><a href="pushconf">智能推送</a></li>
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
                                <input type="checkbox" name="pushconf[day_read]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['pushconf']['day_read']) checked @endif >
                            </div>
                        </div>
                        <div class="title">继续阅读提醒</div>
                    </div>
                    <div>
                        <div>开启后系统将向当日阅读过书籍、并且未阅读时间超过
                            <input class="input-num" type="number" max="12" min="1" name="day_read" @if(isset($wechat['pushconf']['day_read']) && $wechat['pushconf']['day_read']) value="{{$wechat['pushconf']['day_read']}}" @else value="8" @endif>
                            小时的用户。<span class="tip-color">推送时间：14:00 - 22:00</span></div>
                        <div class="relative margin-top20">
                            <img src="/img/wechat/msg/a3dbc28d7a35.jpg">
                            <div class="absolute">最近阅读历史的未读完书籍，否则推送用户性别派单指数>=95的书籍</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="pushconf[first_recharge]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['pushconf']['first_recharge']) checked @endif >
                            </div>
                        </div>
                        <div class="title">首充优惠图文推送</div>
                    </div>
                    <div>
                        <div>开启后如果用户下单，但是30分钟内未支付，会给他推送一条首次充值优惠的客服图文消息。</div>
                        <div class="relative margin-top20">
                            <img src="/img/wechat/msg/75012e5e19e1.jpg">
                            <div class="absolute">亲，首次充值仅需9.9元，还送您900个书币，点击前往！</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="pushconf[sign]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['pushconf']['sign']) checked @endif >
                            </div>
                        </div>
                        <div class="title">签到图文推送</div>
                    </div>
                    <div>
                        <div>开启后系统将向签到用户推送相应的客服消息。用户签到12小时后。<span class="tip-color">推送时间：14:00 - 22:00</span></div>
                        <div class="relative margin-top20">
                            <img src="/img/wechat/msg/f0c7f85f214f.jpg">
                            <div class="absolute">图文简介</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="pushconf[nopay]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['pushconf']['nopay']) checked @endif >
                            </div>
                        </div>
                        <div class="title">未支付提醒</div>
                    </div>
                    <div>
                        <div>开启后系统将自动向符合条件的用户推送相应模板消息。用户下单30分钟后。</div>
                        <div class="relative margin-top20">
                            <img src="/img/wechat/msg/8d8b370975aa.jpg">
                            <div class="absolute">未支付提醒</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="pushconf[subs12h]" value="12" lay-skin="switch" lay-text="ON|OFF" @if(isset($wechat['pushconf']['subs12h']) && $wechat['pushconf']['subs12h']) checked @endif >
                            </div>
                        </div>
                        <div class="title">新用户且是关注的
                            <input class="input-num" type="number" max="12" min="1" name="subs12h" @if(isset($wechat['pushconf']['subs12h']) && $wechat['pushconf']['subs12h']) value="{{$wechat['pushconf']['subs12h']}}" @else value="12" @endif>
                            小时内推送推荐小说的客户消息</div>
                    </div>
                    <div>
                        <div>开启后系统将自动向符合条件的用户推送相应模板消息。用户下单30分钟后。</div>
                        <div class="relative margin-top20">
                            <p>新用户且是关注的12小时内推送推荐小说的客户消息</p><br><br>
                            <p>👉<a style="color: #3b87f8;" target="_blank" href="#">{{$subscribe_msg_12h['title'][0]}}</a></p><br><br>
                            <p>👉<a style="color: #3b87f8;" target="_blank" href="#">{{$subscribe_msg_12h['title'][1]}}</a></p><br><br>
                            <p>👉<a style="color: #3b87f8;" target="_blank" href="#">{{$subscribe_msg_12h['title'][2]}}</a></p><br><br>
                            <p>👉<a style="color: #3b87f8;" target="_blank" href="#">{{$subscribe_msg_12h['title'][3]}}</a></p><br><br>
                            <div style="text-align: right;">
                                <a class="layui-btn layui-btn-normal" href="publish24" style="">配置小说</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




            {{--<div class="col-lg-4 col-md-6 col-sm-12">
                <div class="item">
                    <div>
                        <div class="layui-form-item abs-right">
                            <div class="layui-input-block">
                                <input type="checkbox" name="pushconf[nopay]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['pushconf']['nopay']) checked @endif >
                            </div>
                        </div>
                        <div class="title">未支付提醒</div>
                    </div>
                    <div>
                        <div>开启后系统将自动向符合条件的用户推送相应客服消息；推送时间：在未支付订单产生时间的下一个整十分推送，最多发送一次。<span class="tip-color">推送时间：00:00 - 24:00</span></div>
                        <div class="relative margin-top20">
                            <div style="border: 1px solid #ddd;padding: 5px;">
                                <p>亲：您的书币订单还未完成支付，现在充值49元可获得7000书币，赶紧行动吧！</p><br>
                                <p class="tip-color">(立即充值)</p><br>
                                <p>猜您喜欢</p><br>
                                <p class="tip-color">“陌路柔情：狂少轻点吻”</p><br>
                                <p class="tip-color">“浴火猫妖”</p><br>
                                <p class="tip-color">“笙歌王妃”</p><br>
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
                                <input type="checkbox" name="pushconf[readed8h]" value="1" lay-skin="switch" lay-text="ON|OFF" @if($wechat['pushconf']['readed8h']) checked @endif >
                            </div>
                        </div>
                        <div class="title">继续阅读</div>
                    </div>
                    <div>
                        <div>开启后系统将向当日阅读过书籍、并且未阅读时间超过8小时的用户。<span class="tip-color">推送时间：14:00 - 22:00</span></div>
                        <div class="relative margin-top20">
                            <div style="border: 1px solid #ddd;padding: 5px;">
                                <p>欢迎回来~</p><br>
                                <p class="tip-color">点击继续上次的阅读</p><br>
                                <p>热门推荐</p><br>
                                <p class="tip-color">“废掉一个渣男，一个试纸就够”</p><br>
                                <p class="tip-color">“男朋友把我剩的神仙水送给前任”</p><br>
                                <p class="tip-color">“女孩手腕长出动物骨节，村民嘲讽后意外 身亡，死因竟是”</p><br>
                                <p class="tip-color">“女孩手腕长出动物骨节，村民嘲讽后意外 身亡，死因竟是”</p><br>
                                <p>更多精彩，请点击<span class="tip-color">书城首页</span> </p><br>
                                <p>小编倾情推荐！直接点击上面您喜欢的内容就可以阅读啦~</p><br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>--}}
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
