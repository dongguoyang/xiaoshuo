<?php

namespace App\Admin\Extensions\Grids\Buttons;

use Encore\Admin\Grid\Tools\BatchAction;

class AllDoCheckRows extends BatchAction {
	protected $action;

	public function __construct($action) {
		$this->action = $action;
	}

	public function script() {
		return <<<EOT

$('{$this->getElementClass()}').on('click', function() {
    swal({
        title: "确定要执行批量操作吗？",
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
                    url: '/administrator/allrelease',
                    data: {
                        "_token": LA.token,
                        "ids": selectedRows(),
                        "rejectstr": {$this->action}
                    },
                    success: function (data) {
						if (data.status == 1) {
							$.pjax.reload('#pjax-container');
							swal(data.info, '', 'success');
						} else {
							swal(data.info, '', 'error');
						}
                    }
                });
			});
        }
    });

});

EOT;

	}
}