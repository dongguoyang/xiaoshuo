<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\CouponLog;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class CouponLogRepository extends Repository {
	public function model() {
		return CouponLog::class;
	}

	public function UserCoupons($user_id, $valid) {
	    $key = config('app.name') . 'user_coupons_' . $user_id . '_'. $valid;

	    return Cache::remember($key, 1440, function () use ($user_id, $valid) {
	        $couponRep = new CouponRepository();
	        if ($valid == 'valid') {
                $list = $this->model
                    ->with('coupon:'.implode(',', $couponRep->model->fields))
                    ->where('user_id', $user_id)
                    ->where('status', 1)
                    ->where('end_at', '>', time())
                    ->select($this->model->fields)
                    ->get();
            } else {
                $list = $this->model
                    ->with('coupon:'.implode(',', $couponRep->model->fields))
                    ->where('user_id', $user_id)
                    ->where(function ($query) {
                        $query->orWhere('status', 0)->orWhere('end_at', '<', time());
                    })->select($this->model->fields)
                    ->get();
            }

	        return $this->toArr($list);
        });
    }
    public function ClearCache($user_id) {
        // $valid = ['valid', 'invalid']
        $key = config('app.name') . 'user_coupons_' . $user_id . '_valid';
        $key2 = config('app.name') . 'user_coupons_' . $user_id . '_invalid';

        Cache::forget($key);
        Cache::forget($key2);
    }
}