<meta name="csrf-token" content="{{ csrf_token()}}">
<link rel="stylesheet" type="text/css" href="/layui/css/layui.css" />
<script src="/layui/layui.js" charset="utf-8"></script>
<script src="/layui/jq.js"></script>
<link rel="stylesheet" href="/layui/css/layui.css">
<link rel="stylesheet" href="/layui/css/app.css">

<link rel="stylesheet" href="/layui/css/select2.min.css">
<style>
    .logoitem {
        padding: 10px 0;
        height: 43px;
        line-height: 43px;
        text-align: center;
    }

    .logoitem.aa {
        width: 60px;
    }

    .logoitem a {
        height: 43px !important;
    }
    .choosechannel{left: -110px;}

</style>
<style>
    .layui-table td,.layui-table th  {
        max-width:  290px;
        min-width:  300px;
        word-wrap:  break-word;
        word-break:  normal;
    }
</style>

<div style="margin-top: 50px"></div>
<form class="layui-form" action="">
    <div class="layui-form-item">
        <label class="layui-form-label">关键词</label>
        <div class="layui-input-inline">
            <input type="text" id="keyword" name="keyword" required  lay-verify="required" placeholder="请输入关键词" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item" id="cons">
        <label class="layui-form-label">回复类型</label>
        <div class="layui-input-block">
            <input type="radio" name="type" value="1" title="文字"  lay-filter="jiedian" checked>
            <input type="radio" name="type" value="2" title="图文" lay-filter="jiedian" >
        </div>
    </div>
    <div class="layui-form-item layui-form-text" id="text">
        <label class="layui-form-label">内容</label>
        <div class="layui-input-block">
            <textarea name="reply_content" id="reply_content" placeholder="请输入内容" class="layui-textarea"></textarea>
        </div>
    </div>
    <div id="image">
        <div class="news" style="">
            <div class="imgT-penl" style="margin-left:100px;">
                <div class="wx-penl">
                    <div class=" item">
                        <input type="text"  name="title" class="layui-input J_Title" placeholder="图文标题">
                    </div>
                    <div class="img-box">
                        <input type="hidden" name="image" value="">
                        <img class="J_Image" id="img" src="https://img.yuanyuezhuishu.com/uploads/cover/20181027/f14e79d090a5.jpg" alt="" width="100%">
                        <div class="zz">
                            <a class="layui-btn" id="test1">更换图片</a>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;" class=" item">
                        <input type="text"  name="description" id="desc" class="layui-input" placeholder="输入图文简介25字内" value="">
                    </div>

                    <div class="item">
                        <input type="text"  name="url" id="url" class="layui-input J_Title" placeholder="图片地址">
                    </div>

                </div>
            </div>
        </div>


    </div>

    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" lay-submit lay-filter="*">立即提交</button>
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
    </div>
</form>




<input type="hidden" id="url" value="">
<script>

    //Demo
    layui.use(['form','upload'], function(){

        var form = layui.form;
        form.render();
        var $ = layui.jquery,upload = layui.upload;
        $("#image").hide();
        $('#test1').click(function () {
            $('.img-ing').removeClass('img-ing');
            $(this).parents('.img-box').find('img').addClass('img-ing');
            change_img = layer.open({
                type: 2,
                area: ['1200px','500px'],
                title: '选择图片',
                content: ['/administrator/wechat_msg_blade_layeropen',true],
                end:function(){
                   var sr= getCookie('src-a');
                   if(sr){
                       $("#img").attr('src',sr);
                       delCookie('src-a'); //删除cookie
                   }

                }
            });
            return false;
        });
        form.on('radio(jiedian)', function(data){
              if( data.value == 1){  //文字
                  // $("#cons").append('<div class="layui-form-item layui-form-text"> <label class="layui-form-label">文字内容</label> <div class="layui-input-block"> <textarea name="reply_content" placeholder="请输入内容" class="layui-textarea"></textarea></div></div> ');
                   $("#text").show();
                   $("#image").hide();
              }else{
                  $("#text").hide();
                  $("#image").show();
              }
            });

        form.on('submit(*)', function (data) {
            //表单数据formData
            var formData = data.field;
            formData.reply_content=$("#reply_content").val();
            formData.desc=$("#desc").val();
            formData.file=$("#img").attr('src');
            formData.url=$("#url").val();
            console.log(formData);
            $.ajax({
                type: "POST",//规定传输方式
                url: "/administrator/wechat_msg_blade_insert",//提交URL
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {data:formData},//提交的数据
                success: function(data){
                    var data = jQuery.parseJSON(data);
                    console.log(data);
                    if(data.code == 200){
                        layer.msg('添加成功');
                        setTimeout(function(layer){
                            window.parent.location.reload();//刷新父页面
                            parent.layer.closeAll();  //疯狂模式，关闭所有层
                        },2000);

                    }else{
                        layer.msg(data.msg);
                    }
                }
            });
          return false;
        })




    });
    function getCookie(name)
    {
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");

        if(arr=document.cookie.match(reg))

            return unescape(arr[2]);
        else
            return null;
    }

    function delCookie(name)
    {
        var exp = new Date();
        exp.setTime(exp.getTime() - 1);
        var cval=getCookie(name);
        if(cval!=null)
            document.cookie= name + "="+cval+";expires="+exp.toGMTString();
    }


</script>

