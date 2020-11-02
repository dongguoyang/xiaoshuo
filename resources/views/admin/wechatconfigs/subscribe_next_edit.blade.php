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
    <form method="post" accept-charset="UTF-8" id="ajaxform" class="form-horizontal ajaxform" enctype="multipart/form-data">
        <input type="hidden" name="id" value="{{$wechat['id']}}">
        <hr>

        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">推荐小说 1</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <select class="form-control select2" data-for="title1" style="width: 100%;" name="nid[0]" required>
                        <option value="">--- 请选择小说 ---</option>
                        @foreach($novels as $v)
                            <option value="{{$v['id']}}" @if($v['id'] == $next_msg['nid'][0]) selected @endif>{{$v['title']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">小说 1 显示标题</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="15" required type="text" id="title1" name="title[0]" value="@if(isset($next_msg['title'][0])){{$next_msg['title'][0]}}@endif" class="form-control type" placeholder="输入 标题">
                </div>
                <span class="help-block"> <i class="fa fa-info-circle"></i>&nbsp;可自定义输入修改，最长15个字 </span>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
        </div>

        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">推荐小说 2</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <select class="form-control select2" data-for="title2" style="width: 100%;" name="nid[1]" required >
                        <option value="">--- 请选择小说 ---</option>
                        @foreach($novels as $v)
                            <option value="{{$v['id']}}" @if($v['id'] == $next_msg['nid'][1]) selected @endif>{{$v['title']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">小说 2 显示标题</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="15" required type="text" id="title2" name="title[1]" value="@if(isset($next_msg['title'][1])){{$next_msg['title'][1]}}@endif" class="form-control type" placeholder="输入 标题">
                </div>
                <span class="help-block"> <i class="fa fa-info-circle"></i>&nbsp;可自定义输入修改，最长15个字 </span>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
        </div>

        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">推荐小说 3</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <select class="form-control select2" data-for="title3" style="width: 100%;" name="nid[2]" required >
                        <option value="">--- 请选择小说 ---</option>
                        @foreach($novels as $v)
                            <option value="{{$v['id']}}" @if($v['id'] == $next_msg['nid'][2]) selected @endif>{{$v['title']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">小说 3 显示标题</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="15" required type="text" id="title3" name="title[2]" value="@if(isset($next_msg['title'][2])){{$next_msg['title'][2]}}@endif" class="form-control type" placeholder="输入 标题">
                </div>
                <span class="help-block"> <i class="fa fa-info-circle"></i>&nbsp;可自定义输入修改，最长15个字 </span>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
        </div>

        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">底部推荐指引</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="15" required type="text" name="bottom[0]" value="@if(isset($next_msg['bottom'][0])){{$next_msg['bottom'][0]}}@endif" class="form-control type" placeholder="输入 推荐指引文字">
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">底部推荐链接</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input required type="text" name="bottom[1]" value="@if(isset($next_msg['bottom'][1])){{$next_msg['bottom'][1]}}@endif" class="form-control type" placeholder="输入 完整链接地址">
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">底部推荐文字</label>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="15" required type="text" name="bottom[2]" value="@if(isset($next_msg['bottom'][2])){{$next_msg['bottom'][2]}}@endif" class="form-control type" placeholder="输入 推荐文字">
                </div>
            </div>
        </div>

        <div style="text-align: center;padding-bottom: 50px;">
            <input type="reset" value="重置" class="btn btn-warning" style="margin-right: 50px;">
            <input type="submit" value="提交" class="btn btn-success">
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

    var fileinputOptions = {
        language: 'zh', //设置语言
        uploadUrl: 'uploadUrl',
        showUpload: false, //是否显示上传按钮
        showRemove:false,
        overwriteInitial: false,
        showPreview : true,
        dropZoneEnabled: false,
        showCaption: true,//是否显示标题
        allowedPreviewTypes: ['image'],
        allowedFileTypes: ['image'],
        allowedFileExtensions:  ['jpg', 'png', 'gif'],
        maxFileSize : 200,
        maxFileCount: 1,
        initialPreviewAsData: true,
        initialPreview: [
            @if(isset($wechat['subscribe_content']['img']))"{{$wechat['subscribe_content']['img']}}"@endif
        ],
        initialPreviewConfig: [
                @if(isset($wechat['subscribe_content']['img'])){width: "120px", url: "del-file", key: "{{$wechat['subscribe_content']['img']}}"},@endif
        ],
        previewSettings: {
            image: {width: "140px", height: "160px"},
        },
        deleteExtraData: function (previewId, index) {
            var obj = {};
            obj._method = 'get';
            // obj._token = 'csrf_token()}}';
            return obj;
        }
    };


    function newPluginInit() {
        $("body").append('<script type="text/javascript" src="/vendor/laravel-admin/bootstrap-fileinput/js/locals/zh.js"><\/script>');
        $("body").append('<script type="text/javascript" src="/vendor/laravel-admin/jquery-ajaxform/jquery.ajaxform.js"><\/script>');
        $('.fileinput').fileinput(fileinputOptions);
        $(".select2").select2();
    }
    $(function () {
        newPluginInit();

        $(".select2").on('change', function () {
            console.log($(this).find('option:selected').html())
            if ($(this).find('option:selected').val() > 0) {
                $("#" + $(this).data('for')).val($(this).find('option:selected').html())
            }
        })

        // 表单提交
        var enAjaxForm = true;
        $(".ajaxform").ajaxForm({
            url: "#",
            beforeSubmit: ajaxFormBefore,
            success: ajaxFormSuccess,
            error: ajaxFormSuccess,
            dataType: 'json'
        });
        //失败不跳转
        function ajaxFormSuccess(rel) {
            // console.log(rel);
            if (rel.code == 302) {
                setTimeout(function(){
                    $.pjax({url: rel.url, container: '#pjax-container'})
                }, 1000);
            }else {
                // 失败才重置表单为可提交状态
                enAjaxForm = true;
            }
            if (typeof layer == 'undefined') {
                alert(rel.msg);
            } else {
                layer.msg(rel.msg);
            }
        }
        function ajaxFormBefore() {
            if (!enAjaxForm) {
                layer.msg('正在提交...');
                return false;
            } else {
                enAjaxForm = false;
                return true;
            }
        }
    });
</script>
