<link href="/admin/layui/css/layui.css" rel="stylesheet">
<style>
    #app {
        background: #ffffff;
    }

    .layui-form-label {
        width: 25% !important;
    }

    .layui-form {
        padding-top: 25px !important;
    }

    .layui-btn {
        margin-left: 150px !important;
    }

    .layui-input-block {
        margin-left: 0 !important;
        width: 65% !important;
        display: inline-block;
    }

    .layui-body {
        position: static !important;
    }

    .chapter-list .item {
        border: 1px solid #dddddd;
        border-radius: 0 !important;
        border-bottom: none !important;
    }

    .chapter-list .item:last-of-type {
        border-bottom: 1px solid #dddddd !important;
    }

    .chapter-list .item .chapter-item {
        border: none !important;
        border-radius: 0 !important;
    }

    .desc {
        font-size: 14px;
        color: #777;
        margin-top: 10px;
    }

    .title {
        font-size: 18px;
        color: #333;
        margin: 15px 0;
    }
    #contentmodel {
        height: 500px;
        overflow: auto;
        padding: 20px;
        line-height: 28px;
        color: #666;
    }
</style>
<div class="layui-body">
    <!-- 内容主体区域 -->
    <div style="padding: 15px;">
        <h2 class="Title"></h2>
        <p style="margin-bottom: 10px;">
            <span class="layui-breadcrumb" style="visibility: visible;">
                <a href="{{ route('novelList') }}">小说列表</a>
                <a>
                    <cite>获取{{ $promotion_targets[$target] }}链接</cite>
                </a>
            </span>
        </p>
        <div class="layui-row layui-col-space30 fiction-list">
            <div class="layui-col-md3">
                <div class="details">
                    <img src="{{ $novel['img'] }}" alt="" style="max-width: 100%;"
                         onerror="this.src='/admin/img/no-cover.png';">
                    <p class="title">{{ $novel['title'] }}</p>
                    <p class="desc">字数: {{ $novel['word_count'] }} 万字</p>
                    <p class="desc">收费章节: {{ $novel['need_buy_section'] }}</p>
                    <p class="desc">{{ $novel['desc'] }}</p>
                </div>
            </div>
            <div class="layui-col-md9">
                <div class="chapter-list">
                    @foreach ($chapters as $chapter)
                        <div class="item">
                            <li class="list-group-item chapter-item">
                                <span style="display:inline-block;min-width:26px;">{{ $chapter['num'] }}</span>
                                <a href="javascript:void(0);" onclick="lookchapnum({{ $chapter['num'] }});"
                                   class="chapter-title" id="chapnum{{ $chapter['num'] }}">第{{ $chapter['num'] }}
                                    章 {{ $chapter['title'] ?: '' }}</a>
                                <span style="font-size:12px;margin-left:5px"><span
                                        style="color:#777;margin-left:3px;">{{ $chapter['updated_at'] }}</span></span>
                                <div class="links" style="float: right">

                                    @if ($chapter['num'] == $novel['recommend_section'])
                                        <div class="layui-inline">
                                            <span
                                                style=" background-color:#FF5722; color:#fff; padding:1px 3px;">推荐文案章节</span>
                                        </div>
                                    @endif
                                    @if ($chapter['num'] == $novel['subscribe_section'])
                                        <div class="layui-inline">
                                            <span
                                                style=" background-color:#009688; color:#fff; padding:1px 3px;">默认关注</span>
                                        </div>
                                    @endif

                                    @if ($chapter['num'] >= 1 && $chapter['num'] <= 8)
                                        @if($target != 'qrcode')
                                        <div class="layui-inline">
                                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown"
                                               aria-expanded="false">
                                                <i class="layui-icon layui-icon-link"></i>
                                                <span class="original"
                                                      data-original-title="文案内容到当前章节，原文链接为下一章">生成{{ $promotion_targets[$target] }}
                                                    文案</span>
                                                <i class="layui-icon layui-icon-triangle-d"></i>
                                            </a>

                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li>
                                                    <a target="_blank"
                                                       href="{{ route('documentDisplay', ['target' => $target]) }}?chapter_id={{ $chapter['id'] }}">
                                                        <i class="layui-icon layui-icon-fonts-strong"></i> 文字模式
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        @endif
                                    @endif

                                    @if ($chapter['num'] >= 1 && $chapter['num'] < $novel['need_buy_section'])
                                        @if($target != 'qrcode')
                                        <div class="layui-inline">
                                            <div class="layui-inline">
                                                <a title="" href="javascript:void(0);"
                                                   onclick="openlink({{ $chapter['num'] }});"
                                                   id="getlinkbtn{{ $chapter['num'] }}" class="original"
                                                   data-original-title="原文链接为当前章节">
                                                    <i class="layui-icon layui-icon-link"></i>
                                                    获取{{ $promotion_targets[$target] }}链接
                                                </a>
                                            </div>
                                        </div>
                                        @else
												<a class="btn btn-info btn-xs" href="/administrator/wechatqrcodes/create?novel_id={{$novel['id']}}&section={{$chapter['num']}}">生成推广二维码</a>
                                        @endif
                                    @endif

                                    @if ($chapter['num'] == $novel['need_buy_section'])
                                        <div class="layui-inline">
                                            <span
                                                style=" background-color:red; color:#fff; padding:1px 3px;">从此章开始收费</span>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="nowchapnum" value="0">
<div id="contentmodel" style="display:none;"></div>
<div class="edit-model" id="edit-model" style="display: none;">
    <form action="#" class="layui-form" onsubmit="return false;">
        <input type="hidden" id="nid" value="{{ $novel['id'] }}">
        <input type="hidden" id="chapnum" value="0">
        <div class="layui-form-item">
            <label class="layui-form-label">作品名称：</label>
            <div class="layui-input-block">
                <input type="text" id="bookname" value="{{ $novel['title'] }}" disabled="" placeholder=""
                       autocomplete="off" class="layui-input disabled">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">选择章节：</label>
            <div class="layui-input-block">
                <input type="text" id="cname" value="" disabled="" placeholder="" autocomplete="off"
                       class="layui-input disabled">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"
                   style="color:{{ $target_type_map[$target][1] }};">{{ $promotion_targets[$target] }}渠道名称：</label>
            <div class="layui-input-block">
                <input type="text" id="title" placeholder="请输入渠道名称" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">成本（元）：</label>
            <div class="layui-input-block">
                <input type="text" id="cost" placeholder="如：1200"
                       onkeyup="this.value=this.value.replace(/[^0-9-]+/,'');" autocomplete="off" class="layui-input">
            </div>
        </div>
        @if ($target == 'outer')
            <div class="layui-form-item">
                <label class="layui-form-label">强制关注开关：</label>
                <div class="layui-input-block">
                    <input type="checkbox" id="gztype" value="1" lay-skin="switch" checked lay-text="开|关" disabled/>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">强制关注章节：</label>
                <div class="layui-input-block">
                    <input type="text" id="gzchapnum" onkeyup="value=value.replace(/[^1234567890-]+/g,'')"
                           placeholder="可选, 如不填则使用小说默认设置" autocomplete="off" class="layui-input">
                </div>
            </div>
        @endif

        <div class="layui-form-item">
            <label class="layui-form-label">推广链接：</label>
            <div class="layui-input-block">
                <input type="text" id="linkurl" readonly value="" placeholder="http://" autocomplete="off"
                       class="layui-input disabled"/>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="formDemo" id="makelinkbtn" onclick="getthelink();">
                    生成链接
                </button>
                <button class="layui-btn layui-btn-normal" style="display:none;" id="copybtn"
                        data-clipboard-target="#linkurl" onclick="docopyUrl();">复制链接
                </button>
            </div>
        </div>
    </form>
</div>
<div id="poptit" style="display:none;">
    生成{{ $promotion_targets[$target] }}链接
</div>
@if ($target == 'inner')
    <div class="linkbox"><span class="tit">生成内推链接</span><span class="tip">内推链接只能在当前授权公众号内使用，外推使用失效</span></div>
@endif
<script src="/admin/layui/layui.js"></script>
<script src="/admin/js/clipboard.min.js"></script>
<script type="text/javascript">
    var layer;
    layui.use(['laydate', 'element', 'form', 'layer'], function () {
        var form = layui.form;
        var element = layui.element;
        var $ = layui.$;
        var laydate = layui.laydate;
        layer = layui.layer;
    });

    $(document).ready(function () {
        $('body').on('click', '.dropdown-toggle', function (e) {
            e.stopPropagation();//阻止事件向上冒泡

            var _this_obj = $(this);
            $('.layui-inline.open').not($(this).parent('.layui-inline')).removeClass('open');
            $(this).parent('.layui-inline').toggleClass('open');

            $(document).one("click", function () {
                $('.layui-inline.open').not(_this_obj.parent('.layui-inline')).removeClass('open');
                _this_obj.parent('.layui-inline').toggleClass('open');
            });
        });


        $('.original').each(function () {
            var tip = $(this).attr('data-original-title');
            var tipobj = new Object();
            $(this).hover(function () {
                tipobj = layer.tips(tip, this);
            }, function () {
                layer.close(tipobj);
            });
        });
    });

    //查看章节文案
    function lookchapnum(chapnum) {
        $('#nowchapnum').val(chapnum);
        setTimeout(function () {
            var chapnum = $('#nowchapnum').val();
            var nid = $('#nid').val();
            $.ajax({
                type: "POST",
                url: '{{ route('getChapterInfo') }}',
                data: {
                    "novel_id": nid,
                    "chapter_no": chapnum
                },
                dataType: "json",
                success: function (data) {
                    var html = '<p>' + data.content_str + '</p>';
                    $('#contentmodel').html(html);
                    $('#contentmodel').scrollTop(0);
                }
            });

            layer.open({
                title: $('#chapnum' + chapnum).html(),
                type: 1,
                skin: 'contentmodel_layer',
                btn: ['上一章', '下一章', '关闭'],
                success: function (layero, index) {
                    if (chapnum == 1) {
                        layero.find('.layui-layer-btn0').addClass('layui-hide');
                    }
                    if (chapnum > 1) {
                        layero.find('.layui-layer-btn0').removeClass('layui-hide');
                    }
                    if (chapnum < 20) {
                        layero.find('.layui-layer-btn1').removeClass('layui-hide');
                    }
                    if (chapnum >= 20) {
                        layero.find('.layui-layer-btn1').addClass('layui-hide');
                    }
                },
                btn1: function (index, layero) {
                    chapnum = parseInt(chapnum) - 1;

                    if (chapnum == 1) {
                        layero.find('.layui-layer-btn0').addClass('layui-hide');
                    }
                    if (chapnum > 1) {
                        layero.find('.layui-layer-btn0').removeClass('layui-hide');
                    }
                    if (chapnum < 20) {
                        layero.find('.layui-layer-btn1').removeClass('layui-hide');
                    }
                    if (chapnum >= 20) {
                        layero.find('.layui-layer-btn1').addClass('layui-hide');
                    }

                    layer.title($('#chapnum' + chapnum).html(), index);
                    return false;
                },

                btn2: function (index, layero) {
                    chapnum = parseInt(chapnum) + 1;
                    if (chapnum == 1) {
                        layero.find('.layui-layer-btn0').addClass('layui-hide');
                    }
                    if (chapnum > 1) {
                        layero.find('.layui-layer-btn0').removeClass('layui-hide');
                    }
                    if (chapnum < 20) {
                        layero.find('.layui-layer-btn1').removeClass('layui-hide');
                    }
                    if (chapnum >= 20) {
                        layero.find('.layui-layer-btn1').addClass('layui-hide');
                    }

                    layer.title($('#chapnum' + chapnum).html(), index);
                    return false;
                },

                area: '700px',
                content: $('#contentmodel')

            });
        }, 100);


    }

    $(document).on("click", ".contentmodel_layer .layui-layer-btn0", function () {
        var chapnum = $('#nowchapnum').val();
        var nid = $('#nid').val();
        chapnum = parseInt(chapnum) - 1;
        $('#nowchapnum').val(chapnum);
        var txt = $(this).html();
        if (txt == '关闭') {
            return false;
        } else {
            $.ajax({
                type: "POST",
                url: '{{ route('getChapterInfo') }}',
                data: {
                    "novel_id": nid,
                    "chapter_no": chapnum
                },
                dataType: "json",
                success: function (data) {
                    var html = '<p>' + data.content_str + '</p>';
                    $('#contentmodel').html(html);
                    $('#contentmodel').scrollTop(0);
                }
            });
        }
    });


    $(document).on("click", ".contentmodel_layer .layui-layer-btn1", function () {
        var chapnum = $('#nowchapnum').val();
        var nid = $('#nid').val();
        chapnum = parseInt(chapnum) + 1;
        $('#nowchapnum').val(chapnum);
        var txt = $(this).html();
        if (txt == '关闭') {
            return false;
        } else {
            $.ajax({
                type: "POST",
                url: '{{ route('getChapterInfo') }}',
                data: {
                    "novel_id": nid,
                    "chapter_no": chapnum
                },
                dataType: "json",
                success: function (data) {
                    var html = '<p>' + data.content_str + '</p>';
                    $('#contentmodel').html(html);
                    $('#contentmodel').scrollTop(0);
                }
            });
        }
    });


    //生成推广链接弹窗
    function openlink(chapnum) {
        if (chapnum > 0) {
            var cname = $('#chapnum' + chapnum).html();
            $('#chapnum').val(chapnum);
            $('#cname').val(cname);
            $('#title').val(''); //重置渠道名称
            $('#gztype').prop("checked", true); //重置强关开关打开
            $('#gzchapnum').val(''); //重置关注章节
            $('#linkurl').val(''); //重置推广链接
            $('#getlinkbtn' + chapnum).html(
                '<img src="/admin/img/loading.gif" style="width:16px;height:16px;"> 生成{{ $promotion_targets[$target] }}链接');

            //初始化按钮
            $('#makelinkbtn').removeClass('layui-btn-disabled').removeAttr('disabled').html('生成链接').show();
            $('#copybtn').hide();

            //重新渲染开关
            layui.use(['form'], function () {
                var form = layui.form;
                form.render();
            });

            //初始化按钮
            $('#makelinkbtn').removeClass('layui-btn-disabled').removeAttr('disabled').html('生成链接');
            $('#copybtn').hide();

            var poptit = $('#poptit').html();

            layer.open({
                type: 1,
                title: poptit,
                skin: 'layer-openlink',
                area: ['600px'],
                //btn: ['生成链接'],
                content: $('#edit-model')
                /*,yes: function(index, layero){
                    //do something
                    alert(nid);
                    layer.close(index); //如果设定了yes回调，需进行手工关闭
                }*/
            });

            $('#getlinkbtn' + chapnum).html('<i class="layui-icon layui-icon-link"></i> 获取{{ $promotion_targets[$target] }}链接');

        } else {
            alert('请选择对应章节');
        }

    }

    //生成渠道链接 makelinkbtn
    function getthelink() {
        var nid = $('#nid').val();
        var chapnum = $('#chapnum').val();
        var title = $('#title').val();
        var cost = $('#cost').val();
        var isauthorized = $('#gztype').is(':checked') ? 0 : 2;
        var gzchapnum = $('#gzchapnum').val();
        var isloading = $('#makelinkbtn').is(':disabled');
        var type = '{{ $target_type_map[$target][0] }}';
        //console.log(isauthorized);

        if (title == '') {
            layer.msg('请填写渠道名称');
            return false;
        }

        //if(gzchapnum != '' && gzchapnum > '7'){
//			layer.msg('填写的强制关注章节不能大于默认关注的第7章节');
//			return false;
//		}

        $('#makelinkbtn').html('<img src="/admin/img/loading.gif" style="width:16px;height:16px;"> 创建生成中...').addClass(
            'layui-btn-disabled').attr('disabled');
        $('#copybtn').hide();


        //获取此书章节信息
        $.ajax({
            type: "POST",
            url: '{{ route('createPromotionLink') }}',
            data: {
                "novel_id": nid,
                "chapter_no": chapnum,
                "ctitle": title,
                "type": type,
                "cost": cost,
                "subscribe_on": isauthorized,
                "subscribe_section": gzchapnum
            },
            dataType: "json",
            success: function (data) {
                if (data.err_code == 0) {
                    $('#linkurl').val(data.data.link);
                    $('#copybtn').show();
                    //生成成功了不让再生成了
                    $('#makelinkbtn').hide();
                } else {
                    if (data.err_code == 301) {
                        layer.closeAll();
                        noappidRoad(data.err_msg, data.data.redirect);
                    } else {
                        layer.msg(data.err_msg);
                    }
                    $('#makelinkbtn').html('生成链接').removeClass('layui-btn-disabled').removeAttr('disabled');
                }
            }
        });

    }

    //全局初始化的时候就设定好复制，否则=点击的时候初始化需要点2下才能开始复制
    var copyurl = document.getElementById('copybtn');
    var url = $('#linkurl').val();
    var clipboard = new Clipboard(copyurl, {
        text: function () {
            return url;
        }
    });
    var copynum = 0;

    //复制链接
    function docopyUrl() {
        var url = $('#linkurl').val();
        clipboard.on('success', function (e) {
            //e.clearSelection();
            //避免后续点击，执行复制次数越来越多
            copynum++;
            if (copynum >= 1) {
                clipboard.destroy();
                clipboard = new Clipboard(copyurl);
            }
            console.log('复制成功');
            layer.msg('链接复制成功');
        });
    }

    //复制链接
    function copyUrl_bak() {

        var url = $('#linkurl').val();
        if (url == '') {
            return false;
        }
        var mb = myBrowser();
        if (mb != 'IE') {
            //selectText($(this)[0]);//文本类文字的选中
            $('#linkurl').select();
            layer.msg('非IE内核浏览器请使用ctrl+c进行复制，已经帮您选中链接');
            return false;
        }
        window.clipboardData.setData("Text", url);
        layer.msg('复制成功，请使用ctrl+v粘贴到您需要用到的地方。');
    }

    //选中文本
    function selectText(obj) {
        //var obj = document.getElementById('copy');
        if (document.selection) {
            var range = document.body.createTextRange();
            range.moveToElementText(obj);
            range.select();
        } else if (window.getSelection) {
            var range = document.createRange();
            range.selectNode(obj);
            window.getSelection().addRange(range);
        }
    }

    //浏览器判断
    function myBrowser() {
        var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
        var isOpera = userAgent.indexOf("Opera") > -1;
        if (isOpera) {
            return "Opera";
        } //判断是否Opera浏览器
        if (userAgent.indexOf("Firefox") > -1) {
            return "FF";
        } //判断是否Firefox浏览器
        if (userAgent.indexOf("Chrome") > -1) {
            return "Chrome";
        }
        if (userAgent.indexOf("Safari") > -1) {
            return "Safari";
        } //判断是否Safari浏览器
        if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera) {
            return "IE";
        } //判断是否IE浏览器
    }

    //未设置公众号配置的弹窗提示引导
    function noappidRoad(msg, url) {
        msg = msg ? msg : '您的公众号配置不完整，推广前请务必配置好公众号信息';
        url = url ? url : '';
        var cfm = layer.confirm(msg, {
            btn: ['立即配置', '我先看看'] //按钮
            ,
            skin: 'myconfirm_layer'
        }, function () {
            //alert('页面跳转'+url);
            window.location.href = url;
            layer.close(cfm);
        }, function () {
            //取消了
        });
    }
</script>
<div class="layui-layer-move"></div>
