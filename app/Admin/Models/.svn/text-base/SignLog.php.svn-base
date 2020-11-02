<?php
namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class SignLog extends Base {
    protected $table = 'sign_logs';
    protected $fillable = ['customer_id', 'platform_wechat_id', 'user_id', 'continue_day', 'coin', 'updated_at', 'created_at'];
    public $fields = ['id', 'customer_id', 'platform_wechat_id', 'user_id', 'continue_day', 'coin', 'updated_at', 'created_at'];


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