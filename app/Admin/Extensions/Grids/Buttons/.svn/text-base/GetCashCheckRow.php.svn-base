<?php

namespace App\Admin\Extensions\Grids\Buttons;

use Encore\Admin\Admin;

class GetCashCheckRow
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        return <<<SCRIPT

$('.enable_get_cash').on('click', function () {
    var money = $(this).data('money') / 100;
    if(confirm('确定同意该用户申请提现 '+money+' 元？')) {
        var id = $(this).data('id');
        $.get("/administrator/guessmoney/cash/allow", { id: id },
            function(data){
                if(data.err_code == 0) {
                    if(confirm('同意提现操作成功，重新加载页面？')) {
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

$('.unable_get_cash').on('click', function () {
    var money = $(this).data('money') / 100;
    if(confirm('确定驳回该用户提现申请？')) {
        var id = $(this).data('id');
        $.get("/administrator/guessmoney/cash/unallow", { id: id },
            function(data){
                if(data.err_code == 0) {
                    location.reload();
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
        $GetCash = new \App\Admin\Models\GuessMoney\GetCash;
        $money = $GetCash->select(['money', 'status'])->find($this->id);
        if($money->status==0) {
            $str = " <a class='btn btn-xs btn-danger enable_get_cash' data-id='{$this->id}' data-money='{$money->money}' title='通过审核'><i class='fa fa-slideshare'></i></a> ";
            $str.= " <a class='btn btn-xs btn-info  unable_get_cash' data-id='{$this->id}' title='驳回申请'><i class='fa fa-power-off'></i></a> ";
        } else {
            $str = '';
        }
        return $str;
    }

    public function __toString()
    {
        return $this->render();
    }
}