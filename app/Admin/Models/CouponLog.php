<?php
namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class CouponLog extends Base {
    protected $table = 'coupon_logs';
    protected $fillable = ['customer_id', 'platform_wechat_id', 'user_id', 'coupon_id', 'status', 'start_at', 'end_at', 'updated_at', 'created_at'];
    public $fields = ['id', 'customer_id', 'platform_wechat_id', 'user_id', 'coupon_id', 'status', 'start_at', 'end_at', 'updated_at', 'created_at'];
    protected $dates = ['created_at', 'updated_at', 'start_at', 'end_at'];
    protected $casts = ['created_at' => 'datetime:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s', 'start_at' => 'datetime:Y-m-d H:i:s', 'end_at' => 'datetime:Y-m-d H:i:s'];
    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = Admin::user();
            if(!$customer->isAdministrator()) {
                $builder->where('customer_id', '=', $customer->id);
            } else {
                $builder->where('id', '>', 0);
            }
        });
    }
}