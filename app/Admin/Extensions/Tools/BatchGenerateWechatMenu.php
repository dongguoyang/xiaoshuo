<?php
/**
 * 批量生成微信公众号菜单
 */
namespace App\Admin\Extensions\Tools;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class BatchGenerateWechatMenu extends AbstractTool {
    protected function script() {
        return <<<SCRIPT
$('.batch-generate-menu').on('click', function () {
    swal({
        title: '确认生成所有公众号的自定义菜单？',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonColor: '#DD6B55',
        confirmButtonText: '确认',
        showLoaderOnConfirm: true,
        preConfirm: function() {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    url: '/administrator/wckeepers/batch-generate/menu',
                    data: {
                        _token: LA.token
                    },
                    success: function (data) {
                        resolve(data);
                    }
                });
            });
        }
    }).then(function(result) {
        console.log(result);
        var data = result.value;
        if (typeof data === 'object') {
            if (data.err_code == 0) {
                $.pjax.reload('#pjax-container');
                swal(data.err_msg, '', 'success');
            } else {
                swal(data.err_msg, '', 'error');
            }
        } else {
            swal('已处理', '', 'success');
        }
    });
});
SCRIPT;
    }

    public function render() {
        Admin::script($this->script());
        return "<a class='btn btn-xs btn-success batch-generate-menu' title='生成所有菜单'><i class='fa fa-sitemap'></i></a>";
    }

    public function __toString() {
        return $this->render();
    }
}