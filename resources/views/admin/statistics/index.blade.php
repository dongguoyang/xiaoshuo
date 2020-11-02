<link href="/admin/layui/css/layui.css" rel="stylesheet">
<style type="text/css">
    html {
        min-width: 1170px;
        color: #333;
    }
    h2.Title {
        margin-bottom: 15px;
    }
    .onemoneytj .mntj1 {
        background-color: cornsilk;
    }
    .meirshouyihuiz .layui-col-md3 .onemoneytj .header {
        padding: 15px 0 12px 0;
        height: 76px;
        box-sizing: border-box;
        color: #333;
        text-indent: 76px;
    }
    .meirshouyihuiz .onemoneytj .header {
        background: url(/img/money_bg11.png) 18px center no-repeat;
        background-size: 40px;
        border: 1px #e6e6e6 solid;
    }
    .meirshouyihuiz .layui-col-md3 .onemoneytj .header .title {
        font-size: 14px;
    }
    .meirshouyihuiz .layui-col-md3 .onemoneytj .header .money {
        font-size: 22px;
        color: #FF5722;
    }
    .meirshouyihuiz .layui-col-md3 .onemoneytj .main {
        border: 1px #e6e6e6 solid;
        border-top: none;
        text-align: left;
        padding: 20px 0 20px 20px;
        box-sizing: border-box;
        font-size: 14px;
        line-height: 22px;
        color: #797979;
    }
    .meirshouyihuiz .layui-col-md3 .onemoneytj .main .Num {
        border-top: 1px solid #ddd;
        padding-top: 10px;
        margin-top: 10px;
    }
    .icon-input {
        padding-left: 24px;
    }
    .input-icon.date {
        top: 11px;
    }
    .input-icon {
        position: absolute;
        top: 10px;
        left: 6px;
        font-size: 14px;
    }
    .iconfont {
        font-family: "iconfont" !important;
        font-size: 16px;
        font-style: normal;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .icon-rili:before {
        content: "&#xe637";
    }
    .lab-check {
        display: inline-block;
        cursor: pointer;
        height: 38px;
        line-height: 38px;
    }
    .pagebar2 {
        position: relative;
        width: 100%;
        padding: 7px 7px 0;
        border-width: 1px 0 0;
        text-align: center;
    }
    .pagebar .pagination {
        height: 30px;
        text-align: center;
    }
    .pagebar .pagination li {
        display: inline-block;
        overflow: hidden;
        margin: 0 3px;
        *display: inline;
        *zoom: 1;
        line-height: 30px;
        color: #666;
        font-size: 12px;
        padding: 0px;
    }
    .pagebar .pagination li a, .pagebar .pagination li span {
        display: inline-block;
        vertical-align: top;
        width: auto;
        height: 28px;
        text-align: center;
        line-height: 28px;
        font-size: 12px;
        color: #666;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 0 10px;
        background: #fff;
    }
    .pagebar .pagination li.active span, .pagebar .pagination li a:hover {
        color: #fff;
        background: #009688;
        border: 1px solid #009688;
    }
    .readonly, .disabled {
        background-color: #f1f1f1;
    }
    .disabled {
        cursor: not-allowed !important;
    }
    .pagebar .pagination li.disabled span {
        color: #b3b3b3;
        background-color: #eee;
    }
</style>
<div class="layui-body" style="margin: 105px 35px 80px 50px;border-top: 2px solid #FF5722;">
    <!-- 内容主体区域 -->
    <div style="padding: 15px;">
        <h2 class="Title">每日收益汇总</h2>
        <div class="meirshouyihuiz ">
            <div class="layui-row layui-col-space20">
                <div class="layui-col-md3">
                    <div class="onemoneytj mntj1" style="background-color: cornsilk;">
                        <div class="header">
                            <p class="title">
                                今日充值
                            </p>
                            <p class="money">￥{{ round($today['recharge_money'] / 100, 2) }}</p>
                        </div>
                        <div class="main">
                            <p style="color: #FF5722;">昨日此时充值：￥{{ round($today['yestoday_recharged_sum'] / 100, 2) }}</p>
                            <p>今日分成：￥{{ round($today['deduct_money'] / 100, 2) }}</p>
                            <p>已支付：{{ $today['paid_num'] }}笔</p>
                            <p>未支付：{{ $today['unpay_num'] }}笔</p>
                            <p>充值成功率：{{ $today['pay_success_ratio'] }}%</p>

                            <div class="Num">
                                <p>新用户：
                                    <font>{{ $today['user_num'] }}</font>人
                                </p>
                                <p>新关注：
                                    <font>{{ $today['subscribe_num'] }}</font>人
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="onemoneytj mntj1" style="background-color: cornsilk;">
                        <div class="header">
                            <p class="title">
                                昨日充值
                            </p>
                            <p class="money">￥{{ round($yesterday['recharge_money'] / 100, 2) }}</p>
                        </div>
                        <div class="main">
                            <p>昨日分成：￥{{ round($yesterday['deduct_money'] / 100, 2) }}</p>
                            <p>已支付：{{ $yesterday['paid_num'] }}笔</p>
                            <p>未支付：{{ $yesterday['unpay_num'] }}笔</p>
                            <p>充值成功率：{{ $yesterday['pay_success_ratio'] }}%</p>
                            <div class="Num">
                                <p>新用户：
                                    <font>{{ $yesterday['user_num'] }}</font>人
                                </p>
                                <p>新关注：
                                    <font>{{ $yesterday['subscribe_num'] }}</font>人
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="onemoneytj mntj1" style="background-color: cornsilk;">
                        <div class="header">
                            <p class="title">
                                本月充值
                            </p>
                            <p class="money">￥{{ round($month['recharged_sum'] / 100, 2) }}</p>
                        </div>
                        <div class="main">
                            <p>本月分成：￥{{ round($month['deduct_sum'] / 100, 2) }}</p>
                            <p>已支付：{{ $month['paid_sum'] }}笔</p>
                            <p>未支付：{{ $month['unpaid_sum'] }}笔</p>
                            <p>充值成功率：{{ $month['pay_success_ratio'] }}%</p>
                            <div class="Num">
                                <p>新用户：
                                    <font>{{ $month['user_sum'] }}</font>人
                                </p>
                                <p>新关注：
                                    <font>{{ $month['subscribe_sum'] }}</font>人
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="onemoneytj mntj1" style="background-color: cornsilk;">
                        <div class="header">
                            <p class="title">
                                累计充值
                            </p>
                            <p class="money">￥{{ round($all['recharged_sum'] / 100, 2) }}</p>
                        </div>
                        <div class="main">
                            <p>累计分成：￥{{ round($all['deduct_sum'] / 100, 2) }}</p>
                            <p>已支付：{{ $all['paid_sum'] }}笔</p>
                            <p>未支付：{{ $all['unpaid_sum'] }}笔</p>
                            <p>充值成功率：{{ $all['pay_success_ratio'] }}%</p>
                            <div class="Num">
                                <p>新用户：
                                    <font>{{ $all['user_sum'] }}</font>人
                                </p>
                                <p>新关注：
                                    <font>{{ $all['subscribe_sum'] }}</font>人
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h6 style="margin-top: 20px;font-weight: 600;font-size: 16px;margin-bottom: 10px;">每日充值统计数据</h6>
        <div class="contentbox">
            <div class="listbox">
                <form action="" class="lay  layui-form search_filter" style="margin-top: 20px;" onsubmit="return checkTime();" method="get">
                    <div class="layui-form-item">

                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <input type="text" class="icon-input layui-input" name="start_date" id="startDate" autocomplete="off" placeholder="开始时间" lay-key="1" value="{{ ($params['start_date'] ?? '') }}" />
                                <i class="layui-icon date input-icon layui-icon-date"></i>
                            </div>
                            <div class="layui-form-mid">-</div>
                            <div class="layui-input-inline">
                                <input type="text" class="icon-input layui-input" name="end_date" id="endDate" autocomplete="off" placeholder="结束时间" lay-key="2" value="{{ ($params['end_date'] ?? '') }}" />
                                <i class="layui-icon date input-icon layui-icon-date"></i>
                            </div>
                            <div class="layui-input-inline" style="width:auto;">
                                <label class="lab-check">
                                    <input type="checkbox" name="with_deduct" id="withDeduct" lay-ignore="" value="1" title="只看有推广金额的" data-filtered="filtered" style="display: inline-block;" @if (isset($params['with_deduct']) && $params['with_deduct'] == 1) checked @endif />
                                    <span>只看有推广金额的</span>
                                </label>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <input class="layui-btn" type="submit" value="查询">

                            <button class="layui-btn layui-btn-primary batch_export" data-action="export">导出数据</button>
                        </div>
                    </div>

                </form>
                <table class="layui-table">
                    <thead>
                    <tr>
                        <th class="txt_l">日期</th>
                        <th class="txt_l">充值金额</th>
                        <!--<th>分成金额</th>-->
                        <th>当日注册会员人数</th>
                        <th>当日注册会员充值</th>
                        <th>当日注册会员当日充值</th>
                        <th>充值数据分析</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['list'] as $record)
                    <tr>
                        <td class="txt_l">{{ $record['date_belongto'] }}</td>
                        <td class="txt_l">
                            <span style="color: #FF5722">￥{{ round($record['recharge_money'] / 100, 2) }}</span>
                        </td>
                        <td class="txt_l">
                            <span style="color: #FF5722">{{$record['today_user_number']}}</span>
                        </td>
                        <td>
                            <!--<span style="color: #FF5722">￥{{ round($record['deduct_money'] / 100, 2) }}</span>-->
                            <span style="color: #FF5722">￥{{ round($record['today_user_pay'] / 100, 2) }}</span>
                        </td>
                        <td>
                            <!--<span style="color: #FF5722">￥{{ round($record['deduct_money'] / 100, 2) }}</span>-->
                            <span style="color: #FF5722">￥{{ round($record['today_user_todat_pay'] / 100, 2) }}</span>
                        </td>
                        <td>
                            <div style="margin-right: 40px;float: left;">
                                <p>充值笔数：{{ $record['bill_recharge_count'] }}笔</p>
                                <p>充值人数：{{ $record['recharged_user_count'] }}人</p>
                                <p>未支付：{{ $record['unpay_num'] }}笔</p>
                            </div>
                            <div>
                                <p>充值成功率：{{ $record['pay_success_ratio'] }}%</p>
                                <p>人均消费：￥  {{ round($record['average_consumption'] / 100, 2) }}  </p>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="pagebar pagebar2">
                    <ul class="pagination">
                        <ul class="pagination">
                    @foreach ($page_arr as $page)
                        @if (!empty($page['href']))
                            <li><a href="{{ $page['href'] }}">{{ $page['text'] }}</a></li>
                        @else
                            <li class="{{ $page['class'] }}"><span>{{ $page['text'] }}</span></li>
                        @endif
                    @endforeach
                        </ul>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/admin/layui/layui.js"></script>
<script type="text/javascript">
    var layer;
    layui.use(['laydate', 'element', 'form', 'layer'], function () {
        var form = layui.form;
        var element = layui.element;
        var $ = layui.$;
        var laydate = layui.laydate;
        layer = layui.layer;

        var nowTime = new Date().valueOf();

        var start = laydate.render({
            elem: '#startDate',
            type: 'date',
            min: '2018-11-20',
            max: nowTime,
            //btns: ['clear', 'confirm'],
            done: function (value, date) {
                endMax = end.config.max;
                end.config.min = date;
                end.config.min.month = date.month - 1;
            }
        });
        var end = laydate.render({
            elem: '#endDate',
            type: 'date',
            min: '2018-11-20',
            max: nowTime,
            done: function (value, date) {
                if ($.trim(value) == '') {
                    var curDate = new Date();
                    date = {
                        'date': curDate.getDate(),
                        'month': curDate.getMonth() + 1,
                        'year': curDate.getFullYear()
                    };
                }
                start.config.max = date;
                start.config.max.month = date.month - 1;
            }
        });
    });

    //检测时间
    function checkTime(){
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
        if(startDate && endDate){
            if(startDate > endDate){
                layer.msg('结束时间必须比开始时间大');
                return false;
            }
        }
    }

    $("body").on("click", ".batch_export", function() {
        if(false === checkTime()) {
            return false;
        }
        var form_data   = $('.search_filter').serializeArray();
        var str         = '';

        $.each(form_data, function() {
            if(this.value)
                str += (this.name+'='+this.value + '&');
        });

        if( confirm('是否确认进行数据导出') ){
            var action_url  = $(this).attr('data-action');
            window.open(action_url + "?" + str + 'p={{ $params['p'] }}') ;
            return true;
        }
        return false;
    });
</script>
