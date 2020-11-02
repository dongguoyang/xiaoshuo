<link href="/admin/layui/css/layui.css" rel="stylesheet">
<div class="layui-body">
    <!-- 内容主体区域 -->
    <div style="padding: 30px;">
        <h2 class="layui-text" style="margin-bottom: 15px;">客服互动消息--{{ $customer['name'] }} </h2>
        <div class="layui-tab layui-tab-brief"  lay-filter="tasks">
            <div class="layui-tab-content" style="padding: 40px 10px 10px 15px;">
                <div class="layui-tab-item layui-show">
                    <form action="" class="layui-form" id="search-customer-form">
                        <div class="layui-form">
                            <div class="layui-input-inline">
                                <a href="add" class="layui-btn">添加任务</a>
                                <font style="color:#cccccc;font-size: 12px">* 仅 48 小时内和公众号有过交互 (点击菜单, 回复等) 的粉丝才能收到</font>
                                <font style="color:red;font-size: 12px">&nbsp;&nbsp;当前活跃人数：{{ $act_count }} </font>
                            </div>
                            <div class="layui-input-inline" style="float: right">
                                <a href="javascript:void(0);" id="search-msg-btn" class="layui-btn">搜索</a>
                            </div>
                            <div class="layui-input-inline" style="float: right;margin-right: 10px;">
                                <input type="text" placeholder="输入任务名称关键字" class="layui-input" name="q" id="task_name" value="">
                            </div>
                        </div>
                    </form>
                    <table lay-filter="msg-table" id="imlist"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>
<script src="/layui/layui.js"></script>
<script type="text/javascript">
    layui.use('table', function(){
        var table = layui.table;
        var tables=table.render({
            elem: '#imlist'
            ,url:'list'

            ,limit: 10

            ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            ,cols: [[ //表头
                {field: 'name', title: '任务名称', width:'20.9%'}
                ,{field: 'type', title: '任务类型', width:'10.5%'
                    ,templet: function(d) {
                        var type_name = '';
                        switch (d.type) {
                            case 1:
                                type_name = '图文消息';
                                break;
                            case 2:
                                type_name = '文本消息';
                                break;
                            default:
                                type_name = '未知类型';
                                break;
                        }
                        return type_name;
                    }
                }
                ,{field: 'send_at', title: '发送时间', width:'17.2%'
                    ,templet: function(d) {
                        return 0 != d.status?getLocalTime(d.send_at):'';
                    }
                }
                ,{field: 'status', title: '发生状态', width: '10.5%'
                    ,templet: function(d) {
                        var stat_text = '';
                        switch (d.status) {
                            case -1:
                                stat_text = '<span class="layui-badge layui-bg-red">发送失败</span>';
                                break;
                            case 0:
                                stat_text = '<span class="layui-badge layui-bg-orange">待发送</span>';
                                break;
                            case 1:
                                stat_text = '<span class="layui-badge layui-bg-green">已完成</span>';
                                break;
                        }
                        return stat_text;
                    }
                }
                ,{field: 'total_success', title: '成功', width: '7.1%'}
                ,{field: 'total_failure', title: '失败', width: '7.1%'}
                ,{field: 'end_at', title: '发生结束时间', width: '19.5%'
                    ,templet: function(d) {
                        console.log(d.send_at);
                        return 0 != d.status?getLocalTime(d.end_at):'';
                    }
                }
                ,{field: 'right', title: '操作',toolbar: '#barDemo',width: '7.2%'}
            ]]
            ,page: true //开启分页
        });
        $("#search-msg-btn").click(function(){
              var r= $("#task_name").val();

              if(r){
                  var options={
                      elem: '#imlist'
                      ,url:'list'
                      ,where: {'q':r}
                      ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
                      ,cols: [[ //表头
                          {field: 'name', title: '任务名称', width:'20.9%'}
                          ,{field: 'type', title: '任务类型', width:'10.5%'
                              ,templet: function(d) {
                                  var type_name = '';
                                  switch (d.type) {
                                      case 1:
                                          type_name = '图文消息';
                                          break;
                                      case 2:
                                          type_name = '文本消息';
                                          break;
                                      default:
                                          type_name = '未知类型';
                                          break;
                                  }
                                  return type_name;
                              }
                          }
                          ,{field: 'send_at', title: '发送时间', width:'17.2%'
                              ,templet: function(d) {
                                  console.log(d.send_at);
                                  return 0 != d.status?getLocalTime(d.send_at):'';
                              }
                          }
                          ,{field: 'status', title: '发生状态', width: '10.5%'
                              ,templet: function(d) {
                                  var stat_text = '';
                                  switch (d.status) {
                                      case -1:
                                          stat_text = '<span class="layui-badge layui-bg-red">发送失败</span>';
                                          break;
                                      case 0:
                                          stat_text = '<span class="layui-badge layui-bg-orange">待发送</span>';
                                          break;
                                      case 1:
                                          stat_text = '<span class="layui-badge layui-bg-green">已完成</span>';
                                          break;
                                  }
                                  return stat_text;
                              }
                          }
                          ,{field: 'total_success', title: '成功', width: '7.1%'}
                          ,{field: 'total_failure', title: '失败', width: '7.1%'}
                          ,{field: 'end_at', title: '发生结束时间', width: '19.5%'
                              ,templet: function(d) {
                                  console.log(d.send_at);
                                  return 0 != d.status?getLocalTime(d.end_at):'';
                              }
                          }
                          ,{field: 'right', title: '操作',toolbar: '#barDemo',width: '7.2%'}
                      ]]
                  };
                  table.reload("imlist", options);
              }else{
                   tables.reload({
                             where: {'q':''}
                           , page: {curr: 1}
                       }
               );
              }
        })
        table.on('tool(msg-table)', function(obj){
            var data = obj.data;
            console.log(obj)
            if(obj.event === 'del'){
                layer.confirm('确认删除?', function(index){
                    obj.del();
                    var id=data.id;
                    $.post('delete_list',{id:id},function (res) {
                         var res=JSON.parse(res);
                         //console.log(res);
                         layer.msg(res.msg);
                    });
                    layer.close(index);
                });
            } else if(obj.event === 'edit'){

            }
        });

    });
    function getLocalTime(dataTime) {
        var date= new Date();
        date.setTime(dataTime);
        //获取当前时间的小时
        var hours = date.getHours();
        //分钟
        var min = date.getMinutes();
        //秒
        var sec = date.getSeconds();
        //年
        var year = date.getFullYear();
        //月    月份的范围是从0~11,所以获得的月份要加1才是当前月
        var mon = date.getMonth();
        //日
        var day = date.getDate();
        var formatTime = year+"-"+(mon+1)+"-"+day+" "+hours+":"+min;
        return formatTime;

    }


</script>