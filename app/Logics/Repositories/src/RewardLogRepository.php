<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\RewardLog;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class RewardLogRepository extends Repository {
	public function model() {
		return RewardLog::class;
	}

	/**
	 * 获取打赏记录
     * @param int $novel_id
     * @param int $page
     * @param string $order_column
     */
	public function RewardList($novel_id, $page, $order_column) {
	    if ($order_column == 'coin') {
	        // 获取打赏记录的榜单数据；只获取第一页
	        $page = 1;
        }
	    $key = config('app.name'). 'reward_list_'.$novel_id.'_'.$page.'_'.$order_column;

	    return Cache::remember($key, 60, function () use ($novel_id, $page, $order_column) {
	        $list = $this->model
                ->where('novel_id', $novel_id)
                ->orderBy($order_column, 'desc')
                ->offset($this->pagenum * ($page - 1))
                ->limit($this->pagenum)
                ->select($this->model->fields)
                ->get();

	        return $this->toArr($list);
        });
    }

    /**
     * 获取用户发出的打赏记录
     * @param int $user_id
     * @param int $page
     */
    public function UserLogs($user_id, $page) {
        $key = config('app.name'). 'reward_log_user_'.$user_id.'_'.$page;

        return Cache::remember($key, 60, function () use ($user_id, $page) {
            $list = $this->model
                ->where('user_id', $user_id)
                ->orderBy('id', 'desc')
                ->offset($this->pagenum * ($page - 1))
                ->limit($this->pagenum)
                ->select($this->model->fields)
                ->get();

            return $this->toArr($list);
        });
    }

    public function ClearCache($novel_id, $user_id){
        // $order_column = ['created_at', 'coin_num']
        // $key = config('app.name'). 'reward_list_'.$novel_id.'_'.$page.'_'.$order_column;
        $page = 1;
        $key = config('app.name'). 'reward_list_'.$novel_id.'_';
        $key2 = config('app.name'). 'reward_log_user_'.$user_id.'_';
        while ($page < 10) {
            Cache::forget($key . $page . '_coin_num');
            Cache::forget($key . $page . '_created_at');
            Cache::forget($key2 . $page);
            $page++;
        }
    }
}