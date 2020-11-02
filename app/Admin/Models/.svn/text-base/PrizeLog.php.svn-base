<?php
namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class PrizeLog extends Base {
    protected $table = 'prize_logs';
    protected $fillable = ['customer_id', 'platform_wechat_id', 'prize_id', 'user_id', 'username', 'prize_name', 'desc', 'status', 'updated_at', 'created_at'];
    public $fields = ['id', 'customer_id', 'platform_wechat_id', 'prize_id', 'user_id', 'username', 'prize_name', 'desc', 'status', 'updated_at', 'created_at'];


    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('customer_id', '=', $customer->id);
            }
        });
    }
}