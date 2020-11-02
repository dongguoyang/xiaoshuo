<div class="box">
{{--    <hr style="border-color: #fff;margin: 0;">--}}
    <ul class="nav nav-pills">
        <li class="active"><a href="index">授权信息</a></li>
        <li><a href="subscribe">外推关注回复</a></li>
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
        #grid-table5de27571e536a td>div {margin: 10px}
        #grid-table5de27571e536a td:nth-child(2)>div>div {margin: 10px auto;font-weight: bold;}
        #grid-table5de27571e536a td:first-child {width: 150px;vertical-align: middle;}
        #grid-table5de27571e536a td:nth-child(2) {width: 150px;text-align: left;}
        #grid-table5de27571e536a td:nth-child(3) {text-align: left;}
        #grid-table5de27571e536a td:nth-child(3) div {height: 40px;line-height: 40px;}
        #grid-table5de27571e536a td:nth-child(3) span {margin-right: 15px;margin-top: -3px;}
    </style>
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover" id="grid-table5de27571e536a">
            <tbody style="text-align: right;">
            <tr>
                <td class="column-__row_selector__">
                    <div>公众号：</div>
                </td>
                <td class="column-id">
                    <div><img src="{{$wechat['img']}}" style="width: 80px;border-radius: 40px;"></div>
                </td>
                <td class="column-customer_id">
                    <div>
                        <div>
                            <span style="font-size: 16px;">{{$wechat['name']}}</span>
                            <span class="btn btn-info btn-xs">@if(isset($wtypes[$wechat['type']])) {{$wtypes[$wechat['type']]}} @else 服务号 @endif</span>
                            <span class="btn btn-success btn-xs">已认证</span> </div>
                        <div style="font-size: 17px;">appID：{{$wechat['appid']}}</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="column-__row_selector__">
                    <div>授权状态：</div>
                </td>
                <td class="column-id">
                    <div>
                        {{--@if($wechat['origin_id'])
                            <span class="btn btn-success btn-xs">已授权</span>
                        @else
                            <a href="{{route('platform.auth')}}" target="_blank" class="btn btn-danger">重新授权</a>
                        @endif--}}
                    </div>
                </td>
                <td class="column-customer_id">
                </td>
            </tr>
            <tr>
                <td class="column-__row_selector__" style="vertical-align: top;">
                    <div>权限列表：</div>
                </td>
                <td class="column-id">
                    <div>
                        {{--@if($wechat['origin_id'])--}}
                            <div>消息管理权限√</div>
                            <div>自定义菜单权限√</div>
                            <div>网页服务权限√</div>
                            <div>群发与通知权限√</div>
                            <div>用户管理权限√</div>
                            <div>帐号服务权限√</div>
                            <div>素材管理权限√</div>
                            <div>微信多客服权限√</div>
                        {{--@else
                            <div>消息管理权限×</div>
                            <div>自定义菜单权限×</div>
                            <div>网页服务权限×</div>
                            <div>群发与通知权限×</div>
                            <div>用户管理权限×</div>
                            <div>帐号服务权限×</div>
                            <div>素材管理权限×</div>
                            <div>微信多客服权限×</div>
                        @endif--}}
                    </div>
                </td>
                <td class="column-customer_id">
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
