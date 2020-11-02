<?php
/**
 * Created by PhpStorm.
 * User: byhenry
 * Date: 2019/2/15
 * Time: 11:58 AM
 */

namespace App\Admin\Extensions\Grids\Buttons;

use App\Admin\Models\User;
use Encore\Admin\Admin;

class UserToggleFacke {
	protected $current_user_id;
	protected $is_faker;

	public function __construct($current_user_id, $is_faker) {
		$this->current_user_id = $current_user_id;
		$this->is_faker = $is_faker;
	}

	protected function script() {
		return <<<SCRIPT

$('.toggle-facke').on('click', function () {
    var user_id = $(this).data('id');
    var is_facker = $(this).data('facker');
    swal({
        title: "确认操作当前用户【ID：" + user_id + "】为疑似刷子？注：此操作将同步到该用户下级",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "确认",
        showLoaderOnConfirm: true,
        cancelButtonText: "取消",
        preConfirm: function() {
            return new Promise(function(resolve) {
                $.ajax({
                    method: 'post',
                    url: '/administrator/users/toggle_facke',
                    data: {
                        id: user_id,
                        _token:LA.token,
                    },
                    success: function (data) {
                        resolve(data);
                    }
                });
            });
        }
    }).then(function(result) {
        var data = result.value;
        if (typeof data === 'object') {
            if (data.status == 1) {
                $.pjax.reload('#pjax-container');
                swal(data.info, '', 'success');
            } else {
                swal(data.info, '', 'error');
            }
        }
    });
});

SCRIPT;
	}

	protected function render() {
		Admin::script($this->script());
		$current_user_id = $this->current_user_id;
		$is_faker = $this->is_faker;
		return "<a class='btn btn-xs btn-success toggle-facke' data-id='{$current_user_id}' data-facker='{$is_faker}' title='疑似刷子'><i class='fa fa-adjust'></i></a>";
	}

	public function __toString() {
		return $this->render();
	}
}