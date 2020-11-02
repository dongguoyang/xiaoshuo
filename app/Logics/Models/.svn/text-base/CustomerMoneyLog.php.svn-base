<?php

namespace App\Logics\Models;

class CustomerMoneyLog extends Base
{
    protected $table = 'customer_money_logs';

    protected $fillable = ['customer_id', 'platform_wechat_id', 'user_id', 'recharge_log_id', 'money', 'balance', 'status',
        ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'user_id', 'recharge_log_id', 'money', 'balance', 'status',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }


}