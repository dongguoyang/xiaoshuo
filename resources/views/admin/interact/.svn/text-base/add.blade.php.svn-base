<link href="/admin/layui/css/layui.css" rel="stylesheet">
<style type="text/css">
    .imgT-penl {
        width: 420px;
        padding: 10px;
        background: #e5e5e5;
    }
    .imgT-penl .wx-penl {
        background: #fff;
        padding: 10px;
    }

    .img-box {
        margin: 0 !important;
    }

    .img-box img {
        display: block;
        width: 100%;
    }

    .imgT-penl .wx-penl .img-box {
        width: 100%;
        position: relative;
        margin: 15px 0;
    }
    .imgT-penl .wx-penl .img-box:hover .zz {
        display: block;
    }
    .imgT-penl .wx-penl .img-box .zz {
        position: absolute;
        display: none;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, .3);
        text-align: center;
    }
    .imgT-penl .wx-penl .img-box .zz a {
        position: absolute;
        top: 50%;
        margin-top: -19px;
        left: 50%;
        margin-left: -46px;
    }
    .imgT-penl .items .item {
        border-top: 1px solid #eee;
        padding: 6px 0;
        display: flex;
    }

    .imgT-penl .items .item .desc {
        flex: 1
    }

    .imgT-penl .items .item .desc>p {
        width: 100%;
        height: auto;
        word-wrap: break-word;
        word-break: break-all;
        overflow: hidden;
    }

    .imgT-penl .items .item img {
        height: 50px;
        margin-left: 30px;
    }

    .imgT-penl .items .item .link {
        color: red;
    }

    .imgT-penl .items .item .link.s {
        color: #ccc;
    }

    .imgT-penl .add-sub-item-panel {
        text-align: center;
        margin-top: 15px;
    }
    .app-link {
        color: #009688;
    }

    .layui-form-label {
        width: auto;
    }

    .layui-tab-content {
        padding-left: 0;

    }

    .wx-penl {
        background: #fff;
        width: 400px;
    }

    .mission-name {
        margin-bottom: 0 !important;
    }

    #task_name {
    }

    .edit-img-list {
        /* max-height: 250px; */
        overflow-y: scroll;
        height: 250px;
    }
    .edit-img-list li {
        float: left;
        width: 20%;
        padding: 10px;
        height: 50px;
        overflow: hidden;
    }

    .my-form {
        padding-top: 15px;
        padding-left: 15px;
    }

    #nav {
        padding-left: 20px;
        margin-top: 30px;
        color: #333;
    }

    #nav > .layui-breadcrumb {
        font-size: 20px;
        visibility: visible;
    }

    .layui-body {
        background: #ffffff;
    }
    .app-link:hover {
        color: #009688;
        text-decoration: underline;
    }
</style>
<div class="layui-body">
    <!-- 内容主体区域 -->
    <div style="padding: 30px;">
        <p id="nav">
            <span class="layui-breadcrumb" lay-separator="/">
                <a href="index">客户互动消息</a>
                <a>
                    <cite>添加客服互动消息任务</cite>
                </a>
            </span>
        </p>

        <form class="layui-form my-form">
            <div class="layui-form-item">
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title mode">
                        <li class="layui-this" data-class="graphic">图文</li>
                        <li  data-class="word">文本</li>
                    </ul>
                </div>
            </div>

            <div class="layui-form-item mission-name">
                <div class="layui-input-block" style="margin-left: 0">
                    <div class="layui-input-inline" style="width:420px">
                        <input title="text" name="task_name" value="" class="layui-input" id="task_name" placeholder="任务名称" required />
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show graphic">
                        <div class="layui-input-block" style="margin-top: 10px;margin-left: 0">
                            <div class="imgT-penl">
                                <div class="wx-penl">
                                    <div class=" item">
                                        <input value="" type="text" name="title" class="layui-input J_Title"  placeholder="图文标题" >
                                        <label class="error title-error"></label>
                                    </div>
                                    <div class="img-box">
                                        <input type="hidden" name="image" value="">
                                        <img src="https://novelsys.oss-cn-shenzhen.aliyuncs.com/public/morepic.png?x-oss-process=image/resize,m_fixed,h_165,w_380" alt="" width="100%" class="J_Image">
                                        <div class="zz">
                                            <a class="layui-btn" id="test1">更换图片</a>
                                        </div>
                                        <label class="error image-error"></label>
                                    </div>
                                    <div style="margin-bottom: 15px;" class=" item">
                                        <input type="text" name="description" id="description" class="layui-input" placeholder="输入图文简介25字内" value="">
                                    </div>
                                    <input  value="" type="text" name="url" class="layui-input J_Link" placeholder="原文链接">
                                    <label class="error url-error"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-tab-item  word">
                        <div class=" item">
                            <div id="aaa">
                                <textarea id="textcnt" name="content" maxlength="600" class="layui-input" style="height: 200px;width: 600px;margin-bottom: 20px;padding: 10px"></textarea>
                                <h6 id="word" style="width: 600px;text-align: right;position: relative;margin-top: -21px;margin-right: 10px;color: #666;">0/600</h6>
                                <label class="error content-error"></label>
                            </div>
                        </div>
                        <a href="#this" class="layui-btn layui-btn-primary addlink">+ 插入链接</a>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">发送对象</label>
                <div class="layui-input-block">
                    <div class="layui-inline">
                        <input type="radio" lay-filter="fans"  name="fans" value="1"   title="全部粉丝" checked>
                        <input type="radio" lay-filter="fans"  name="fans" value="2"   title="按分类选择" >
                        <ul id="vive" class="vive" id="content" style="background: #f3f3f3;padding: 10px;display: none;;">
                            <li>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">性别：</label>
                                    <div class="layui-input-block">
                                        <div class="layui-inline">
                                            <input type="radio" name="sex" value="0" title="不限" checked>
                                            <input type="radio" name="sex" value="1" title="男" >
                                            <input type="radio" name="sex" value="2" title="女"  >
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">充值情况：</label>
                                    <div class="layui-input-block">
                                        <div class="layui-inline">
                                            <input type="radio" name="recharge" value="0" title="不限" checked>
                                            <input type="radio" name="recharge" value="1" title="已充值粉丝" >
                                            <input type="radio" name="recharge" value="2" title="未充值粉丝" >
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">发送时间</label>
                <div class="layui-input-block">
                    <div class="layui-inline">
                        <input type="text" class="layui-input" id="laydate" style="width:420px">
                        <a href="#" class="add-time-link app-link" style="margin-right: 20px" data-time="30">30分钟后</a>
                        <a href="#" class="add-time-link app-link" style="margin-right: 20px" data-time="60">1个小时后</a>
                        <a href="#" class="add-time-link app-link" style="margin-right: 20px" data-time="180">3个小时后</a>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">测试粉丝ID</label>
                <div class="layui-input-block">
                    <div class="layui-inline" style="width: 314px;">
                        <input type="text" id="user_id" name="title" placeholder="请输入粉丝ID" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <button type="button" id="preview-send" class="layui-btn">发送测试</button>
                    </div>
                    <span style="color: #aaaaaa;font-size: 12px">用户ID如何查看请看
                        <a href="https://novelsys.oss-cn-shenzhen.aliyuncs.com/public/user-example.png" target="_blank" class="app-link">实例图片</a>
                    </span>

                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button id="news-submit" type="button" class="layui-btn" lay-filter="formDemo">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary" onclick="javascript:history.back()">取消</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="model-edit" style="display: none;padding: 18px 18px 0 ;">
    <div class="layui-form">
        <div class="layui-form-item">
            <label class="layui-form-label">图文标题</label>
            <div class="layui-input-block">
                <input type="text" id="typeahead-input" name="title" class="layui-input typeahead edit-title">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">原文链接</label>
            <div class="layui-input-block">
                <input type="text" id="typeahead-input" name="title" class="layui-input typeahead edit-url">
            </div>
        </div>
    </div>
</div>
<div id="model-img-edit" style="display: none;padding: 18px 18px 0 ;">
    <div class="layui-tab layui-tab-card">
        <ul class="layui-tab-title">
            <li class="layui-this">消息封面</li>
            <li>活动封面</li>
            <li>文案封面</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <div class="layui-tab layui-tab-card">
                    <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show">
                            <ul class="edit-img-list" id='edit_img1' style="height:250px;">
                                <li class="item">

                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-tab-item">
                <div class="layui-tab layui-tab-card">
                    <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show">
                            <ul class="edit-img-list" id='edit_img2' style="height:250px;">
                                <li class="item">

                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-tab-item">
                <div class="layui-tab layui-tab-card">
                    <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show">
                            <ul class="edit-img-list" id='edit_img3' style="height:250px;">
                                <li class="item">

                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="add-modal" style="display: none;padding: 20px">
    <div class="layui-form" style="width:400px">
        <div class="layui-form-item">
            <label class="layui-form-label">链接文案</label>
            <div class="layui-input-block">
                <input type="text" name="title" required lay-verify="required" placeholder="请输入标题" autocomplete="off" class="layui-input linktxt">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">链接</label>
            <div class="layui-input-block">
                <input type="text" name="password" autocomplete="off" required lay-verify="required" placeholder="http://" autocomplete="off" class="layui-input lnikurl">
                <p style="margin-top:10px">将生成【<font style="color:red">&#60;a href="链接"&#62链接文案&#60/a&#62;</font>】格式的文字链，可直接复制、删除或修改</p>
            </div>
        </div>
    </div>
</div>
<script src="/layui/jq.js"></script>
<script src="/admin/layui/layui.js"></script>
<script type="text/javascript">
    (function () {
        (function init() {
            layui.use('element', function () {
                var element = layui.element;
            });
            layui.use('form', function () {
                var form = layui.form;
            });
        })()
    })();
        var test_btn_prsd = false;
    $('.addlink').click(function () {
        $('#add-modal').find('.lnikurl').val('');
        $('#add-modal').find('.linktxt').val('');
        var modal = layer.open({
            type: 1,
            area: ['500px', '300px'],
            offset: 'auto',
            content: $('#add-modal'),
            btn: ['确定'],
            yes: function () {
                var url = $('#add-modal').find('.lnikurl').val(),
                    txt = $('#add-modal').find('.linktxt').val();
                if (url === '' || txt === '') {
                    layer.msg('信息填写不完整！');
                } else {
                    if (url.indexOf('http') >= 0) {
                        var text = '<a href="' + url + '">' + txt + '</a>';
                        $('#textcnt').val($('#textcnt').val() + text);
                        layer.close(modal);
                    } else {
                        layer.msg('请输入有效的网址', {icon: 5});
                    }
                    /*$.ajax({
                        url: url,
                        type: "GET",
                        complete:function(res) {
                            if (res.status == 200) {
                                var text = '<a href="' + url + '">' + txt + '</a>';
                                $('#textcnt').insertContent(text);
                                layer.close(modal);
                            } else {
                                layer.msg('请输入有效的网址', {icon: 5});
                            }
                        }
                    });*/
                }
            }
        });
    });
    $('.mode li').click(function () {
        if ($(this).data('class') == 'word') {
            $('.word').addClass('layui-show');
            $('.graphic').removeClass('layui-show');
        } else {
            $('.graphic').addClass('layui-show');
            $('.word').removeClass('layui-show');
        }
    });
    $(function () {
        $("#textcnt").keyup(function () {
            var len = $(this).val().length;
            if (len > 600) {
                $(this).val($(this).val().substring(0, 600));
                $("#word").text(0);
            }
            var num = 600 - len;
            if (num === 0) {
                $("#word").text('600/600');
                $("#word").css('color','red');
            } else {
                $("#word").text(len+'/600');
                $("#word").css('color','#666');
            }
        });
        $("#textcnt").keyup();
    });
    layui.use('form', function () {
        var form = layui.form;
        form.render();
        form.on('radio(fans)', function (data) {
            if (data.value == 1) {
                document.getElementById("vive").style.display = "none";
            } else {
                document.getElementById("vive").style.display = "block";
            }
        });
    });
    $(document).ready(function () {
        $.ajax({
            type : "POST",
            url : "/administrator/api/material/images",
            data : {
                type: 1
            },
            dataType: "json",
            success : function(result) {
                html = '';
                for (var i = 0; i < result.length; i++){
                    html += '<li class="item"><img src="' + result[i]['img'] + '" width="100%" alt=""></li>';
                }
                $('#edit_img1').html(html);
            }
        });
        $.ajax({
            type : "POST",
            url : "/administrator/api/material/images",
            data : {
                type: 2
            },
            dataType: "json",
            success : function(result) {
                html = '';
                for (var i = 0; i <result.length; i++){
                    html += '<li class="item"><img src="' + result[i]['img'] + '" width="100%" alt=""></li>';
                }
                $('#edit_img2').html(html);
            }
        });
        $.ajax({
            type : "POST",
            url : "/administrator/api/material/images",
            data : {
                type: 3
            },
            dataType: "json",
            success : function(result) {
                html = '';
                for (var i = 0; i <result.length; i++){
                    html += '<li class="item"><img src="' + result[i]['img'] + '" width="100%" alt=""></li>';
                }
                $('#edit_img3').html(html);
            }
        });

        $('#test1').click(function () {
            $('.img-ing').removeClass('img-ing');
            $(this).parents('.img-box').find('img').addClass('img-ing');
            layer.open({
                type: 1,
                area: ['600px'],
                title: '选择封面',
                content: $('#model-img-edit')
            });
            return false;
        });
        $('#model-img-edit').on('click', '.item img', function () {
            $('.img-ing').attr('src', $(this).attr('src'));
            $(this).parents('.layui-layer').find('.layui-layer-close').click();
        });

        layui.use('laydate', function () {
            var laydate = layui.laydate;
            laydate.render({
                elem: '#laydate',
                value: new Date(),
                type: 'datetime',
                format: 'yyyy-MM-dd HH:mm',
                min: 0,
                max: 60
            });
        });

        $('.add-time-link').click(function () {
            var time = Date.parse(new Date());
            var addT = $(this).data('time');
            time += addT * 60000;
            $('#laydate').val(new Date(time).Format("yyyy-MM-dd hh:mm"));
        });
    });
    Date.prototype.Format = function (fmt) {
        var o = {
            "M+": this.getMonth() + 1, //月份
            "d+": this.getDate(), //日
            "h+": this.getHours(), //小时
            "m+": this.getMinutes(), //分
            "s+": this.getSeconds(), //秒
            "q+": Math.floor((this.getMonth() + 3) / 3), //季度
            "S": this.getMilliseconds() //毫秒
        };
        if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
        for (var k in o)
            if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[
                k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        return fmt;
    };
    $("#preview-send").click(function () {
        if(test_btn_prsd) {
            return false;
        }
        test_btn_prsd = true;
        var data = [];
        var send_to = $("input[name='fans']:checked").val();
        var task_name = $("#task_name").val();
        var sex = 0;
        var recharge = 0;
        var send_type = {};
        var uid = $("#user_id").val();
        if (typeof uid === undefined || $.trim(uid) == '' || parseInt(uid) < 1) {
            layer.msg('请填写测试用户id');
            test_btn_prsd = false;
            return false;
        }
        var type = '';
        var ischecked = true;
        $('.mode li').each(function(){
            if ($(this).attr('class') == 'layui-this' && $(this).data('class') == 'graphic'){
                data = graphicCheck();
                if (!data) {
                    ischecked = false;
                    test_btn_prsd = false;
                    return false;
                }
                type = 1;

            } else if ($(this).attr('class') == 'layui-this' && $(this).data('class') == 'word') {
                data = textCheck();
                if (!data) {
                    test_btn_prsd = false;
                    ischecked = false;
                    return false;
                }
                type = 2;
            }
        });
        if (!ischecked) {
            test_btn_prsd = false;
            return false;
        }
        if (send_to == 2){
            sex = $("input[name='sex']:checked").val();
            recharge = $("input[name='recharge']:checked").val();
            if (sex == 0 && recharge == 0){
                send_to = 1;
            } else {
                send_to = 2;
                send_type = {
                    sex: sex,
                    recharge: recharge
                };
            }
        }
        $.post("test", {
            name: task_name,
            type: type,
            content: data,
            send_to: send_to,
            send_type: send_type,
            uid: uid
        }, function (res) {
            test_btn_prsd = false;
            if (res.err_code == 0) {
                layer.msg(res.err_msg, {
                    icon: 1
                });
            } else {
                layer.msg(res.err_msg, {
                    icon: 2
                });
            }
        });
    });
    $("#news-submit").click(function () {
        var result = [];
        var type = '';

        $('.mode li').each(function(){
            if ($(this).attr('class') == 'layui-this' && $(this).data('class') == 'graphic'){
                result = graphicCheck();
                type = 1;
            } else if ($(this).attr('class') == 'layui-this' && $(this).data('class') == 'word') {
                result = textCheck();
                type = 2;
            }
        });

        if (!result) {
            return false;
        }

        var date = $("#laydate").val();
        var send_to = $("input[name='fans']:checked").val();
        var task_name = $("#task_name").val();
        var sex = 0;
        var recharge = 0;
        var send_type = {};
        if (send_to == 2){
            sex = $("input[name='sex']:checked").val();
            recharge = $("input[name='recharge']:checked").val();
            if (sex == 0 && recharge == 0){
                send_to = 1;
            } else {
                send_to = 2;
                send_type = {
                    sex: sex,
                    recharge: recharge
                };
            }
        }
        $(this).attr("disabled", "disabled");
        $.ajax({
            type: "POST",
            url: 'doadd',
            data: {
                name: task_name,
                type: type,
                content: result,
                send_to: send_to,
                send_type: send_type,
                send_at: date
            },
            dataType: "json",
            beforeSend: function () {
                $("#news-submit").attr("disabled", "disabled");
                $("#news-submit").text('互动消息任务提交中...');
            },
            success:function (res) {
                if (res.err_code == 0) {
                    layer.msg(res.err_msg, {
                        icon: 1
                    });
                    $("#news-submit").text('已成功提交');
                    setTimeout(function () {
                        window.parent.location.reload();//刷新父页面
                        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                        parent.layer.close(index);
                    }, 2000);
                } else {
                    $("#news-submit").text('重新提交');
                    $("#news-submit").removeAttr("disabled");
                    layer.msg(res.err_msg, {
                        icon: 2
                    });
                }
            }
        });
    });

    function getNewsData() {
        return {
            title: $(".J_Title").val(),
            url: $(".J_Link").val(),
            image: $(".J_Image").attr("src"),
            description: $("#description").val(),
        };
    }

    function graphicCheck() {
        var description = $("#description").val();
        if (description.length > 25) {
            $("#description").css("border-color", "red");
            layer.msg("图文简介25个字内");
            return false;
        }

        if(!$(".J_Title").val())
        {
            $(".J_Title").css("border-color", "red");
            layer.msg("图文标题必须填写");
            return false;
        }

        if($(".J_Image").attr("src") == "https://novelsys.oss-cn-shenzhen.aliyuncs.com/public/morepic.png?x-oss-process=image/resize,m_fixed,h_165,w_380")
        {
            $(".J_Image").css("border-color", "red");
            layer.msg("请选择图片");
            return false;
        }

        var data = getNewsData();
        return data;
    }

    function textCheck() {
        var data = {'content' : $('#textcnt').val()};
        if ($.trim(data.content) == '') {
            layer.msg('请填写文本内容');
            return false;
        }
        return data;
    }
</script>