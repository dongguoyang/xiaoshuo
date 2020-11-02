<?php
namespace App\Admin\Models;


class CustomerMoneyLog extends Base {
    protected $table = 'customer_money_logs';
    protected $fillable = ['customer_id', 'platform_wechat_id', 'user_id', 'recharge_log_id', 'money', 'balance', 'status', 'created_at', 'updated_at'];
    public $fields = ['id', 'customer_id', 'platform_wechat_id', 'user_id', 'recharge_log_id', 'money', 'balance', 'status', 'created_at', 'updated_at'];
}