<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\CoinLog;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class CoinLogRepository extends Repository {
	public function model() {
		return CoinLog::class;
	}

	public function getType($key) {
	    $types = [
            'zan'       => 1,
            'recharge'  => 2,
            'buy'       => 3,
	        'sign'      => 4,
            'prize'     => 5,
            'other'     => 6,
        ];

	    return isset($types[$key]) ? $types[$key] : $types['other'];
    }

    /**
     * 用户书币消费记录
     * @param int $user_id
     * @param int $page
     */
    public function UserLogs($user_id, $page) {
        $key = config('app.name') . 'coin_logs_'.$user_id.'_'.$page;

        return Cache::remember($key, 60, function () use ($user_id, $page) {
            $list = $this->model
                ->where('user_id', $user_id)
                ->where('coin', '<', 0)
                ->offset($this->pagenum * ($page - 1))
                ->limit($this->pagenum)
                ->orderBy('id', 'desc')
                ->select(['user_id', 'type', 'type_id', 'coin', 'balance', 'title', 'desc', 'status', 'updated_at'])
                ->get();
            return $this->toArr($list);
        });
    }

    public function ClearCache($user_id) {
        $key = config('app.name') . 'coin_logs_'.$user_id.'_';

        $page = 1;
        while ($page < 10) {
            if (!Cache::has($key . $page)) {
                break;
            }
            Cache::forget($key . $page);
            $page++;
        }
    }
}