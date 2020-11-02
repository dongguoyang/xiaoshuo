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
        <li><a href="subscribenext">新用户第二次推送</a></li>
        <li><a href="userhpush">每日自定义推送</a></li>
        <li><a href="newmenulist">新菜单设置</a></li>
        <li class="active"><a href="centreMenuList">中部小说菜单栏设置</a></li>
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
        <span class="help-block" style="margin-left: 50px;"> <i class="fa fa-info-circle"></i>&nbsp;2字主角名显示2个表情，3字主角名显示1个表情，4字主角名不显示表情； </span>
        <hr>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">菜单栏标题</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="30"  type="text" id="title0" name="t" value="@if(isset($data['t']) && $data['t']) {{$data['t']}}  @endif" class="form-control type" placeholder="输入 标题">
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
        </div>


        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">小说 1</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <select class="form-control select2" data-for="title0" style="width: 100%;" name="s[0][n]" >
                        <option value="">--- 请选择小说 ---</option>
                        @foreach($novels as $v)
                            <option value="{{$v['id']}}" @if(isset($data['m'][0]['novel_id']) && $v['id'] == $data['m'][0]['novel_id']) selected @endif>{{$v['title']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">标题</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="30"  type="text" id="title0" name="s[0][t]" value="@if(isset($data['m'][0]['name']) && $data['m'][0]['name']) {{$data['m'][0]['name']}}  @endif" class="form-control type" placeholder="输入 标题">
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
        </div>

        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">小说 2</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <select class="form-control select2" data-for="title1" style="width: 100%;" name="s[1][n]" >
                        <option value="">--- 请选择小说 ---</option>
                        @foreach($novels as $v)
                            <option value="{{$v['id']}}" @if(isset($data['m'][1]['novel_id']) && $v['id'] == $data['m'][1]['novel_id']) selected @endif>{{$v['title']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">请选择小说</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="30"  type="text" id="title1" name="s[1][t]" value="@if(isset($data['m'][1]['name']) && $data['m'][1]['name']) {{$data['m'][1]['name']}}  @endif" class="form-control type" placeholder="输入 标题">
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
        </div>

        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">小说 3</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <select class="form-control select2" data-for="title2" style="width: 100%;" name="s[2][n]" >
                        <option value="">--- 请选择小说 ---</option>
                        @foreach($novels as $v)
                            <option value="{{$v['id']}}" @if(isset($data['m'][2]['novel_id']) && $v['id'] == $data['m'][2]['novel_id']) selected @endif>{{$v['title']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">请选择小说</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="30"  type="text" id="title2" name="s[2][t]" value="@if(isset($data['m'][2]['name']) && $data['m'][2]['name']) {{$data['m'][2]['name']}}  @endif" class="form-control type" placeholder="输入 标题">
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
        </div>

        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">小说 4</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <select class="form-control select2" data-for="title3" style="width: 100%;" name="s[3][n]" >
                        <option value="">--- 请选择小说 ---</option>
                        @foreach($novels as $v)
                            <option value="{{$v['id']}}" @if(isset($data['m'][3]['novel_id']) && $v['id'] == $data['m'][3]['novel_id']) selected @endif>{{$v['title']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">请选择小说</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="30"  type="text" id="title3" name="s[3][t]" value="@if(isset($data['m'][3]['name']) && $data['m'][3]['name']) {{$data['m'][3]['name']}}  @endif" class="form-control type" placeholder="输入 标题">
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
        </div>

        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">小说 5</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <select class="form-control select2" data-for="title4" style="width: 100%;" name="s[4][n]" >
                        <option value="">--- 请选择小说 ---</option>
                        @foreach($novels as $v)
                            <option value="{{$v['id']}}" @if(isset($data['m'][4]['novel_id']) && $v['id'] == $data['m'][4]['novel_id']) selected @endif>{{$v['title']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <label for="type" class="col-sm-2  control-label">请选择小说</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input maxlength="30"  type="text" id="title4" name="s[4][t]" value="@if(isset($data['m'][4]['name']) && $data['m'][4]['name']) {{$data['m'][4]['name']}}  @endif" class="form-control type" placeholder="输入 标题">
                </div>
            </div>
        </div>
        <div class="form-group  " style="margin-top: 20px;">
            <hr style="width: 80%;margin-left: 8%;">
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
            if (typeof layer == 'undefined') {
                alert(data.msg);
            }
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
