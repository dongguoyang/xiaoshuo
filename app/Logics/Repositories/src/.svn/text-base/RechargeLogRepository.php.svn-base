<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\RechargeLog;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class RechargeLogRepository extends Repository {
	public function model() {
		return RechargeLog::class;
	}
    /**
     * 用户充值记录
     * @param int $user_id
     * @param int $page
     */
	public function UserLogs($user_id, $page) {
	    $key = config('app.name') . 'recharge_logs_'.$user_id.'_'.$page;

	    return Cache::remember($key, 60, function () use ($user_id, $page) {
	        $list = $this->model
                ->where('user_id', $user_id)
                // ->where('status', 1)
                ->offset($this->pagenum * ($page - 1))
                ->limit($this->pagenum)
                ->orderBy('id', 'desc')
                ->select(['user_id', 'money_btn_id', 'money', 'coin', 'balance', 'status', 'out_trade_no', 'payment_no', 'updated_at', 'desc'])
                ->get();

            $list = $this->toArr($list);
	        foreach ($list as $k=>$v) {
	            $list[$k]['type'] = $this->getType($v['desc']);
            }

	        return $list;
        });
    }
    /**
     * 清除充值记录缓存
     */
    public function ClearCache($user_id) {
        $key = config('app.name') . 'recharge_logs_'.$user_id.'_';
        $page = 1;

        while ($page < 10) {
            if (!Cache::has($key . $page)) {
                break;
            }
            Cache::forget($key . $page);
        }
    }
    /**
     * 设置中奖记录类型和数量
     */
    private function getType($name) {
        if (strpos($name, '优惠券')) {
            $type = 'coupon';
        } elseif (strpos($name, '书币')) {
            $type = 'coin';
        } else {
            $type = 'vip';
        }

        return $type;
    }
}