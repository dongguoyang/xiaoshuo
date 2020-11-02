<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\PrizeLog;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class PrizeLogRepository extends Repository {
	public function model() {
		return PrizeLog::class;
	}
    /**
     * 发放奖品到用户奖品日志
     * @param array $user
     * @param array $prize
     * @return prize_log
     */
	public function ToUser($user, $prize) {
	    $data = [
            'prize_id'  => $prize['id'],
            'user_id'   => $user['id'],
            'username'  => $user['name'],
            'prize_name'=> $prize['name'],
            'desc'      => '用户抽奖',
            'status'    => 1,
            'customer_id'           => $user['customer_id'],
            'platform_wechat_id'    => $user['platform_wechat_id'],
        ];
	    return $this->create($data);
    }

    public function LastPrized()
    {
        $key = config('app.name') . 'last_prized_list';
        return Cache::remember($key, 60, function (){
            $list = $this->model
                ->limit(50)
                ->orderBy('id', 'desc')
                ->select($this->model->fields)
                ->get();
            return $this->toArr($list);
        });
    }
    /**
     * 用户中奖记录
     * @param int $user_id
     * @param int $page
     */
    public function UserPrizeLogs($user_id, $page)
    {
        $key = config('app.name') . 'user_prize_log_'.$user_id.'_'.$page;

        return Cache::remember($key, 1440, function () use ($user_id, $page){
            $list = $this->model
                ->with('prize:id,img')
                ->where('user_id', $user_id)
                ->offset(($page - 1) * $this->pagenum)
                ->limit($this->pagenum)
                ->orderBy('id', 'desc')
                ->select(['prize_id', 'user_id', 'username', 'prize_name', 'desc', 'updated_at'])
                ->get();
            $list = $this->toArr($list);
            foreach ($list as $k=>$v) {
                list($num, $type) = $this->getType2Num($v['prize_name']);
                $list[$k]['type'] = $type;
                $list[$k]['num'] = $num;
            }
            return $list;
        });
    }
    /**
     * 设置中奖记录类型和数量
     */
    private function getType2Num($name) {
        if (strpos($name, '优惠券')) {
            $num = floatval($name);
            $type = 'coupon';
        } elseif (strpos($name, '书币')) {
            $num = intval($name);
            $type = 'coin';
        } else {
            $num = 1;
            $type = 'vip';
        }

        return [$num, $type];
    }

    public function ClearCache($user_id) {
        $key = config('app.name') . 'user_prize_log_'.$user_id.'_';
        $page = 1;
        while ($page < 15) {
            if (!Cache::has($key . $page)) {
                break;
            }
            Cache::forget($key . $page);
            $page++;
        }
    }
}