<?php

namespace App\Admin\Extensions\Grids\Buttons;

use Encore\Admin\Grid\Tools\BatchAction;

class AllRejectRows extends BatchAction {
	protected $action;

	public function __construct($action) {
		$this->action = $action;
	}

	public function script() {
		return <<<EOT

$('{$this->getElementClass()}').on('click', function() {
    swal({
        title: "确定要执行批量驳回提现吗？",
		html: '<br/><span style="color:#000;font-size:15px;"></span><input id="rejectstr" type="text" style="width:100%;height:35px;color:#000;text-align:center;border-radius:5px;border:1px solid #999;" placeholder="驳回原因；该文本框有值就使用文本框值" /><br/><br/><select id="tipinfo" style="height:35px;margin-top:5px;max-width:100%;border-radius:5px;border:1px solid #999;"><option value="">使用上面文本框填写的驳回原因</option><option value="您的有效阅读数量过少！请积极任务或收徒后再次申请提现！">您的有效阅读数量过少！请积极任务或收徒后再次申请提现！</option><option value="请微信实名认证后再次申请提现 ">请微信实名认证后再次申请提现 </option><option value="今日提现次数已达上限！请明日再来提现！">今日提现次数已达上限！请明日再来提现！</option><option value="您的有效阅读过少！请分享文章到群或者朋友圈再次提现！">您的有效阅读过少！请分享文章到群或者朋友圈再次提现！</option></select><br/><br/>',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "确认",
        showLoaderOnConfirm: true,
        cancelButtonText: "取消",
        preConfirm: function() {
			return new Promise(function(resolve) {
				var tipinfo = $("#tipinfo").val();
				var rejectstr = $("#rejectstr").val();
				rejectstr = rejectstr ? rejectstr : tipinfo;
				var ids = selectedRows();
				if(rejectstr == ''){
					swal('请输入驳回原因！', '', 'error');
					return;
				}
				if(ids.length == 0){
					swal('请选择需要驳回的申请', '', 'error');
					return;
				}

                $.ajax({
                    method: 'post',
                    url: '/administrator/allreject',
                    data: {
                        "_token": LA.token,
                        "ids": ids,
                        "rejectstr": rejectstr,
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