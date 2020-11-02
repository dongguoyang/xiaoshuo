<meta name="csrf-token" content="{{ csrf_token()}}">
<link rel="stylesheet" type="text/css" href="/layui/css/layui.css" />
<script src="/layui/layui.js" charset="utf-8"></script>
<script src="/layui/jq.js"></script>
<link rel="stylesheet" href="//bfstatic.roushongkanshu.com/static/ht/static/css/layui.css">
<link rel="stylesheet" href="/layui/css/app.css">
<link rel="stylesheet" href="//bfstatic.roushongkanshu.com/static/ht/static/css/skin_1.css">
<link rel="stylesheet" href="//bfstatic.roushongkanshu.com/static/ht/static/css/select2.min.css">
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

<div id="model-img-edit" style="padding: 18px 18px 0 ;">
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
                            <ul class="edit-img-list" id='edit_img2' style="height:250px;">
                                    @foreach ($data1 as $v1)
                                        <li class="item">
                                            <img src="{{$v1->img}}" width="100%" alt="">
                                        </li>
                                    @endforeach

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
                                @foreach ($data2 as $v2)
                                    <li class="item">
                                        <img src="{{$v2->img}}" width="100%" alt="">
                                    </li>
                                @endforeach

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-tab-item">
                <div class="layui-tab layui-tab-card">
                    <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show">
                            <ul class="edit-img-list" id='edit_img1' style="height:250px;">
                                @foreach ($data3 as $v3)
                                    <li class="item">
                                        <img src="{{$v3->img}}" width="100%" alt="">
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    $(".item img").click(function(){
       var src=$(this).attr('src');
       setCookie('src-a',src);
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index);//关闭当前页
    });
    function setCookie(name,value)
    {
        var Days = 30;
        var exp = new Date();
        exp.setTime(exp.getTime() + Days*24*60*60*1000);
        document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
    }

</script>

