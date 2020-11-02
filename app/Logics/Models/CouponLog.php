<?php

namespace App\Logics\Models;

class CouponLog extends Base
{
    protected $table = 'coupon_logs';

    protected $fillable = [ 'customer_id', 'platform_wechat_id', 'user_id', 'coupon_id', 'status', 'start_at', 'end_at', ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'user_id', 'coupon_id', 'status', 'start_at', 'end_at',
    ];

    public function coupon() {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}