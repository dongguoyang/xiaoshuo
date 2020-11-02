<?php

namespace App\Logics\Models;

class RechargeLog extends Base
{
    protected $table = 'recharge_logs';

    protected $fillable = [
        'customer_id', 'platform_wechat_id', 'user_id', 'money_btn_id', 'money', 'coin', 'balance', 'status',
        'out_trade_no', 'payment_no', 'pay_time', 'type', 'desc', 'view_cid', 'extend_link_id', 'wechat_qrcode_id','pay_wechat_id',
    ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'user_id', 'money_btn_id', 'money', 'coin', 'balance', 'status',
        'out_trade_no', 'payment_no', 'pay_time', 'type', 'desc', 'view_cid', 'extend_link_id', 'wechat_qrcode_id','pay_wechat_id','updated_at',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function moneyBtn() {
        return $this->belongsTo(MoneyBtn::class, 'money_btn_id', 'id');
    }


}