<?php
/**
 * Created by PhpStorm.
 * User: byhenry
 * Date: 2019/1/12
 * Time: 12:11 PM
 */

namespace App\Admin\Extensions\Grids\Buttons;


use Encore\Admin\Admin;

class TestImgCheck
{

    protected function script()
    {
        return <<<SCRIPT

$(".grid-testimg").on('click', function() {
    swal({
        title: "正在执行封面图片检测任务\\r\\n\\r\\n该操作耗时较长，您确定继续吗?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "确定",
        showLoaderOnConfirm: true,
        cancelButtonText: "取消",
        preConfirm: function() {
            return new Promise(function(resolve) {
                $.get('/administrator/testimg/find', function(d){
					swal(d.err_msg);
				},"json");
            });
        }
    });
});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return '<a class="btn btn-sm btn-primary grid-testimg" title="检测封面图片"><i class="glyphicon glyphicon-cloud"></i> 检测封面图片</a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}