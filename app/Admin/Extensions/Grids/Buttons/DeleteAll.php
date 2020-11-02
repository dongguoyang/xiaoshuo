<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/4
 * Time: 17:54
 */

namespace App\Admin\Extensions\Grids\Buttons;

use Encore\Admin\Admin;
class DeleteAll
{
    protected $id;
    protected $name;
    public function __construct($id,$name = '删除')
    {
        $this->id = $id;
        $this->name=$name;
    }

    /*
     *
     *
     *
     */
    protected function script()
    {
        if($this->name=="删除") {
            return <<<SCRIPT
            $('[name="trs"]').on('click', function () { 
            var id= $(this).attr('data-id');
                 swal({
                title: "确认删除?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "确认",
                showLoaderOnConfirm: true,
                cancelButtonText: "取消",
                preConfirm: function(){
                           $.post('/administrator/template_msg/1',{id:id},function(res){
                                    res=JSON.parse(res);
                                    if(res.code == 200){
                                       swal("操作成功!", res.msg, "success");
                                       history.go(0);
                                    }else{
                                      swal("操作失败!", res.msg, "error");
                                    }
                            });
                }
                })
        
            });

SCRIPT;
        }else if($this->name=="踢出"){  //其他情况
            return <<<SCRIPT
  $('[name="trs"]').on('click', function () {
  var id= $(this).attr('data-id');
                 swal({
                title: "确认踢出吗?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "确认",
                showLoaderOnConfirm: true,
                cancelButtonText: "取消",
                preConfirm: function(){
                           $.post('/administrator/delete_groupuser',{id:id},function(res){
                                    res=JSON.parse(res);
                                    if(res.code == 200){
                                       swal("操作成功!", res.msg, "success");
                                       history.go(0);
                                    }else{
                                      swal("操作失败", res.msg, "error");
                                    }
                            });
                }
                })
                
  });

SCRIPT;

        }else if($this->name=='移除'){
            return <<<SCRIPT
  $('[name="trs"]').on('click', function () {
               var id= $(this).attr('data-id');
                 swal({
                title: "确认移除出吗?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "确认",
                showLoaderOnConfirm: true,
                cancelButtonText: "取消",
                preConfirm: function(){
                           $.post('/administrator/interactive-msg/delete_list',{id:id},function(res){
                                    res=JSON.parse(res);
                                    if(res.code == 200){
                                       swal("操作成功!", res.msg, "success");
                                       history.go(0);
                                    }else{
                                      swal("操作失败", res.msg, "error");
                                    }
                            });
                }
                })
                
  });

SCRIPT;
        }else if($this->name=="移除链接"){
            return <<<SCRIPT
  $('[name="trs"]').on('click', function () {
               var id= $(this).attr('data-id');
                 swal({
                title: "确认移除出吗?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "确认",
                showLoaderOnConfirm: true,
                cancelButtonText: "取消",
                preConfirm: function(){
                           $.post('/administrator/extend/delete_extend',{id:id},function(res){
                                    res=JSON.parse(res);
                                    if(res.code == 200){
                                       swal("操作成功!", res.msg, "success");
                                       history.go(0);
                                    }else{
                                      swal("操作失败", res.msg, "error");
                                    }
                            });
                }
                })
                
  });

SCRIPT;
        }else{
            return <<<SCRIPT
  $('[name="trs"]').on('click', function () {
                alert('没有这个操作');
  });

SCRIPT;

            
        }
    }

    protected function render()
    {
        Admin::script($this->script());
        if($this->name=='移除链接'){
            return "<p><a name='trs' class='' data-id='{$this->id}'>$this->name</a></p>";
        }
        return "<button name='trs' class='g btn btn-xs btn-danger' data-id='{$this->id}'>$this->name</button>";
    }

    public function __toString()
    {
        return $this->render();
    }


}