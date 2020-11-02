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
                            <label for="type" class="col-sm-2  control-label">小说名称</label>
                            <div class="col-sm-8">
                                <input id= 'name' class="form-control" style="width: 100%;" name="name" placeholder="请输入小说名称" required>

                            </div>
                        </div>

                        <div class="form-group  ">

                            <label for="type" class="col-sm-2  control-label">cookie</label>
                            <div class="col-sm-8">
                                <input id= 'cookie' class="form-control" style="width: 100%;" name="cookie" placeholder="请输入cookie" required>
                            </div>
                        </div>
                        <div class="form-group  ">

                            <label for="type" class="col-sm-2  control-label">URL</label>
                            <div class="col-sm-8">
                                <input id = 'url' class="form-control" style="width: 100%;" name="url" value="https://c110246.818tu.com" placeholder="请输入URL" required>
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
        var name=$('#name').val();
        var url=$('#url').val();
        var cookie=$("#cookie").val();
        if(name =="" || url=="" ){
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

        pr(name,url,cookie,1);
       function pr(name,url,cookie,page){
            $.post('/administrator/novelscollect', {
                "name": name,
                "url":url,
                "cookie": cookie
            }, function (res) {
                res = JSON.parse(res);
                if(res.code == 200){
                    layer.alert('已经更新完毕', {
                        icon: 1,
                        title: "成功"
                    });
                }
                if(res.code == 101){
                    layer.alert(res.msg, {
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