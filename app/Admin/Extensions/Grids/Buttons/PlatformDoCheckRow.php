<?php

namespace App\Admin\Extensions\Grids\Buttons;

use Encore\Admin\Admin;

class PlatformDoCheckRow
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        return <<<SCRIPT

$('.del-plat').on('click', function () {
    if(confirm('确定删除开放平台和对应平台下的公众号？')) {
        var appid = $(this).data('appid');
        var id = $(this).data('id');
        $.get("/administrator/others/platformdel", { id: id, appid: appid },
            function(data){
                if(data.err_code == 0) {
                    if(confirm('删除成功，重新加载页面？')) {
                        location.reload();
                    }
                } else {
                    alert(data.err_msg);
                }
            }
        , "json");
    }
    // Your code.
    console.log($(this).data('id'));
});


SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());
        $Platform = new \App\Admin\Models\Platform;
        $plat = $Platform->select(['appid'])->find($this->id);

        $str = " <a class='btn btn-xs btn-danger del-plat' data-id='{$this->id}' data-appid='{$plat->appid}' title='删除三方和对应公众号'><i class='fa fa-trash'></i></a> ";
    
        return $str;
    }

    public function __toString()
    {
        return $this->render();
    }
}