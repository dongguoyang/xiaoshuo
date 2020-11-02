<style>
    .my-placeholder {
        color: #999;
        line-height: 34px;
    }
    .myspan{
        color: #999;
        line-height: 34px;
    }
    .mydiv{
        padding-bottom: 50px;
    }
    .my_i{
        color: red;
        padding-right: 5px;
    }
</style>
<div class="box">
<section class="content">
    <div class="row"><div class="col-md-12">
            <form method="POST" action="" class="form-horizontal" accept-charset="UTF-8">
                <div class="box-body fields-group">
                    <div class="mydiv">
                    <p><span class="myspan"><i class="my_i">*</i>1.该功能等待时间是与更新小说的部数成正比</span><br>
                    <span class="myspan"><i class="my_i">*</i>2.在开启该功能时切忌尽量不要关闭该页面</span></p>
                    </div>
                    <div class="form-group  ">

                        <label for="type" class="col-sm-2  control-label">类型</label>

                        <div class="col-sm-8">

                            <select class="form-control type select2" id="type" style="width: 100%;" name="type" data-value="" tabindex="-1" aria-hidden="true">
                                <option value="">请选择</option>
                                <option value="all">全部</option>
                                @foreach ($options as $key=> $user)
                                <option value={{$key}}>{{$user}}</option>
                                @endforeach
                            </select>


                        </div>
                    </div>

                    <div class="form-group  ">

                        <label for="free_start_at" class="col-sm-2  control-label">时间</label>

                        <div class="col-sm-8">


                            <div class="row" style="width: 390px">
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" name="start_at"  class="form-control free_start_at" style="width: 160px">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" name="end_at"  class="form-control free_end_at" style="width: 160px">
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="form-group  ">

                        <label for="returnType" class="col-sm-2  control-label">返回类型</label>

                        <div class="col-sm-8">


                            <input type="hidden" name="returnType">

                            <select class="form-control returnType select2" id="returnType" style="width: 100%;" name="returnType" data-value="" tabindex="-1" aria-hidden="true">
                                <option value="">请选择</option>
                                <option value="1">地址返回</option>
                                <option value="2">内容返回</option>
                            </select>


                        </div>
                    </div>


                </div>


                <!-- /.box-body -->
                <div class="box-footer">
                    <div class="col-md-2"></div>

                    <div class="col-md-8">
                        <div class="btn-group pull-left">
                            <button type="reset" class="btn btn-warning pull-right">重置</button>
                        </div>

                        <div class="btn-group pull-right">
                            <button type="button" id="sub" class="btn btn-info pull-right">提交</button>
                        </div>
                    </div>
                </div>
            </form>
        </div></div>

</section>
</div>

<script src="/layui/layui.js"></script>
<script src="http://www.novel.com/vendor/laravel-admin/moment/min/moment-with-locales.min.js"></script>
<script src="/vendor/laravel-admin/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
<script>
    $(function(){
        $('[name="end_at"]').parent().datetimepicker({"format":"YYYY-MM-DD","locale":"zh-CN","allowInputToggle":true});
        $('[name="start_at"]').parent().datetimepicker({"format":"YYYY-MM-DD","locale":"zh-CN","allowInputToggle":true});
    });


    $("#sub").click(function(){
        var start=$('[name="start_at"]').val();
        var end=$('[name="end_at"]').val();
        var returnType=$("#returnType").val();
        var type=$("#type").val();

        if(start =="" || returnType=="" || type =="" || end == ""){
            layer.alert('参数请输入完整!!', {
                icon: 5,
                title: "提示"
            });
            return false;
        }
        layer.msg('正在更新请勿关闭页面！', {icon: 6});
        layer.load('正在加载');
        $(this).text('请稍等正在更新！！');
        $(this).attr("disabled", true);

        pr(start,end,returnType,type,1);
            function pr(start,end,returnType,type,page){
                $.post('/administrator/get_novel', {
                    "start": start,
                    "end":end,
                    "returnType": returnType,
                    "type": type,
                    "page": page
                }, function (res) {
                    res = JSON.parse(res);
                    if (res.code == 203) {
                        setTimeout(function(){
                            layer.closeAll();
                        }, 2000);

                        layer.alert('已经更新完毕', {
                            icon: 3,
                            title: "成功"
                        });
                        $("#sub").removeAttr("disabled");
                        $("#sub").text('提交');
                        return false;
                    }
                    if (res.code == 205) {
                        setTimeout(function(){
                            layer.closeAll();
                        }, 2000);

                        layer.alert('小说外放接口配置未正确', {
                            icon: 1,
                            title: "失败"
                        });
                        $("#sub").removeAttr("disabled");
                        $("#sub").text('提交');
                        return false;
                    }


                    if(res.code == 200){
                         page=res.page;
                         pr(start,end,returnType,type,page)
                    }
                    if(res.code == 101){
                        layer.alert('服务器不稳定请重试', {
                            icon: 1,
                            title: "失败"
                        });
                        $("#sub").removeAttr("disabled");
                        $("#sub").text('提交');
                        setTimeout(function(){
                            layer.closeAll();
                        }, 3000);
                        return false;
                    }
                })
            }



    })

</script>