<?php
namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class RechargeLog extends Base {
    protected $table = 'recharge_logs';
    protected $fillable = ['customer_id', 'platform_wechat_id', 'user_id', 'money_btn_id', 'money', 'balance', 'status', 'out_trade_no', 'payment_no', 'pay_time', 'type', 'desc', 'created_at', 'updated_at'];
    public $fields = ['id', 'customer_id', 'platform_wechat_id', 'user_id', 'money_btn_id', 'money', 'balance', 'status', 'out_trade_no', 'payment_no', 'pay_time', 'type', 'desc', 'created_at', 'updated_at'];
    protected $dates = ['created_at', 'updated_at',];// 'pay_time'
    protected $casts = ['created_at' => 'datetime:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s',]; //'pay_time' => 'datetime:Y-m-d H:i:s'


    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                //$builder->where('customer_id', '=', $customer->id);
                $builder->where(function ($query) use ($customer){
                    $query->where('customer_id', $customer->id)->orWhere('view_cid', $customer->id);
                });
            }
        });
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function platformWechat() {
        return $this->belongsTo(PlatformWechat::class, 'platform_wechat_id', 'id');
    }
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function moneyBtn() {
        return $this->belongsTo(MoneyBtn::class, 'money_btn_id', 'id');
    }
}