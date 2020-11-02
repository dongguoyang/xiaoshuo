<?php
/**
 * 批量生成微信公众号带参二维码
 */
namespace App\Admin\Extensions\Tools;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class BatchGenerateQRCode extends AbstractTool {
    protected function script() {
        return <<<SCRIPT
$('.batch-generate-qrcode').on('click', function () {
    swal({
        title: '确认生成所有公众号的带参二维码？',
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
                    url: '/administrator/wckeepers/batch-generate/qrcode',
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
        return "<a class='btn btn-xs btn-danger batch-generate-qrcode' title='生成所有二维码'><i class='fa fa-camera-retro'></i></a>";
    }

    public function __toString() {
        return $this->render();
    }
}