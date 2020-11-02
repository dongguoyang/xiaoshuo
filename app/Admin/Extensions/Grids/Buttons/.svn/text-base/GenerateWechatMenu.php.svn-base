<?php
/**
 * 生成微信菜单
 */
namespace App\Admin\Extensions\Grids\Buttons;

use Encore\Admin\Admin;

class GenerateWechatMenu {
    protected $id;
    public function __construct($id) {
        $this->id = $id;
    }

    protected function script() {
        return <<<SCRIPT
$('.generate-menu').on('click', function () {
    var id = $(this).data('id');
    swal({
        title: '确认生成该公众号【ID：' + id + '】的自定义菜单？',
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
                    url: '/administrator/wckeepers/generate/menu',
                    data: {
                        _token: LA.token,
                        id: id
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

    protected function render() {
        Admin::script($this->script());
        return "<a class='btn btn-xs btn-success generate-menu' data-id='{$this->id}' title='生成菜单'><i class='fa fa-sitemap'></i></a>";
    }

    public function __toString() {
        return $this->render();
    }
}