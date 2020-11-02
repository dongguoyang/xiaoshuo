<link rel="stylesheet" type="text/css" href="/layui/layui.css" />

<script src="/layui/layui.js" charset="utf-8"></script>

<div class="btn">
    <button class="btn btn-sm btn-default  pull-right" id="{{$id}}" href="{{$url}}"><i class="fa {{$icon}}"></i> {{$text}}</button>
</div>


<script>

    $("#cors").click(function() {   //微信关键字回复
        layui.use('layer', function () {
            var layer = layui.layer;
            layer.open({
                type: 2,
                title: '关键词添加',
                content: ['/administrator/wechat_msg_blade_insert',true],
                area: ['1200px','800px'],


            })
        });
    });
    $("#addcors").click(function(){  //互动消息
        layui.use('layer',function(){
            var layer=layui.layer;
            layer.open({
                type: 2,
                title: '添加',
                content: ['/administrator/interactive-msg/add',true],
                area: ['1200px','800px'],
            })

        });

    })






</script>

