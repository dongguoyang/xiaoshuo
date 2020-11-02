<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\SignLog;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class SignLogRepository extends Repository {
	public function model() {
		return SignLog::class;
	}

	/**
	 * 获取最近一次签到信息
     * @param int $user_id
     * @param bool $update_cache
     * @return array
     */
	public function LastSignInfo($user_id, $update_cache = false) {
	    $key = $this->getCacheKey($user_id);

	    if ($update_cache && Cache::has($key)) {
	        Cache::forget($key);
        }
        // Cache::forget($key);
	    $today_stamp = strtotime(date('Y-m-d', time()));
	    $minites = intval(($today_stamp + 86400 - time()) / 60); // 今天还剩下多少分钟
	    return Cache::remember($key, $minites, function () use ($user_id, $today_stamp) {
            $info = $this->findByCondition([
                ['user_id', $user_id]
            ], $this->model->fields, ['id', 'desc']);
            $info = $this->toArr($info);
            if (!$info) {
                $info = [
                    'user_id'       => $user_id,
                    'continue_day'  => 0,
                    'created_at'    => 0,
                    'coin'          => 0,
                    'id'            => 0,
                ];
            }

            $info['signed'] = ($info['created_at'] >= $today_stamp) ? true : false;
            if (!$info['signed'] && ($info['created_at'] < ($today_stamp - 86400))) {
                // 今天没签到   并且   上次签到是昨天之前，表示未连续签到，重置连续签到天数
                $info['continue_day'] = 0;
            }

            // 最后一次签到获得的积分数
            $info['last_coin'] = $info['coin'];

            return $info;
        });
    }
    /**
     * 获取签到缓存key
     */
    public function getCacheKey($user_id) {
        return config('app.name') . 'last_sign_' . $user_id;
    }
}