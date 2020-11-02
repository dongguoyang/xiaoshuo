<?php

namespace App\Admin\Extensions\Grids\Buttons;

use Encore\Admin\Admin;

class CashApplyCheck {
	protected $id;
	protected $money;
	protected $status;

	public function __construct($id, $money, $status) {
		$this->id = $id;
		$this->money = $money;
		$this->status = $status;
	}

	protected function script() {
		return <<<SCRIPT

$('.enable_get_cash').on('click', function () {
    var id = $(this).data('id');
    var money = $(this).data('money');
    swal({
        title: "确认同意该用户提现申请"+money+"元?",
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
                    url: '/administrator/getcash_check/'+ id + '?allow=1',
                    data: {
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

$('.unable_get_cash').on('click', function () {
    var id = $(this).data('id');
    var money = $(this).data('money');
    swal({
        title: "确认驳回该用户提现申请"+money+"元?",
        html: '<br/><span style="color:#000;font-size:15px;"></span><input id="rejectstr" type="text" style="width:100%;height:35px;color:#000;text-align:center;border-radius:5px;border:1px solid #999;" placeholder="驳回原因；该文本框有值就使用文本框值" /><br/><br/><select id="tipinfo" style="max-width: 100%; height:35px;margin-top:5px;border-radius:5px;border:1px solid #999;"><option value="">使用上面文本框填写的驳回原因</option><option value="您的有效阅读数量过少！请积极任务或收徒后再次申请提现！">您的有效阅读数量过少！请积极任务或收徒后再次申请提现！</option><option value="请微信实名认证后再次申请提现 ">请微信实名认证后再次申请提现 </option><option value="今日提现次数已达上限！请明日再来提现！">今日提现次数已达上限！请明日再来提现！</option><option value="您的有效阅读过少！请分享文章到群或者朋友圈再次提现！">您的有效阅读过少！请分享文章到群或者朋友圈再次提现！</option></select><br/><br/>',
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
                if(rejectstr == ''){
                    swal("请输入驳回原因");
                    return;
                }

                $.ajax({
                    method: 'post',
                    url: '/administrator/getcash_check/'+ id + '?allow=2',
                    data: {
                        "_token": LA.token,
                        "rejectstr": rejectstr,
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

$('.unlock_get_cash').on('click', function () {
    var id = $(this).data('id');
    var money = $(this).data('money');
    swal({
        title: "确认取消锁定ID-"+id+" ？",
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
                    url: '/administrator/getcash_unlock/'+ id,
                    data: {
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
		$status = $this->status;
		$money = $this->money;
		$money = sprintf('%.2f', $money / 100);
		if ($status == 0) {
			$str = " <a class='btn btn-xs btn-info enable_get_cash' data-id='{$this->id}' data-money='{$money}' title='通过审核'><i class='fa fa-slideshare'></i></a>";
			$str .= " <a class='btn btn-xs btn-danger unable_get_cash' data-id='{$this->id}' data-money='{$money}' title='驳回申请'><i class='fa fa-power-off'></i></a>";
            $str .= " <a class='btn btn-xs btn-warning unlock_get_cash' data-id='{$this->id}' data-money='{$money}' title='取消锁定'><i class='fa fa-unlock'></i></a>";
			return $str;
		}
		return '';
	}

	public function __toString() {
		return $this->render();
	}
}