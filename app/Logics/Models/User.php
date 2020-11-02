<?php

namespace App\Logics\Models;

class User extends Base
{
    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'remember_token', 'invite_code', 'parent1', 'parent2', 'child_num', 'child2_num', 'subscribe',
        'pay_openid', 'openid', 'unionid', 'customer_id', 'platform_wechat_id', 'sign_day', 'img', 'sex', 'recharge_money', 'balance', 'vip_end_at',
        'extend_link_id', 'pay_wechat_up_num', 'view_cid', 'first_account', 'wechat_qrcode_id','source',
    ];

    public $fields = [
        'id',
        'name', 'password', 'subscribe',
        'pay_openid', 'openid', 'unionid', 'customer_id', 'platform_wechat_id', 'sign_day', 'img', 'sex', 'recharge_money', 'balance', 'vip_end_at',
        'extend_link_id', 'pay_wechat_up_num', 'created_at', 'view_cid', 'first_account', 'wechat_qrcode_id','source',
    ];

    public function readLog() {
        return $this->hasMany(ReadLog::class, 'user_id', 'id');
    }

    public function platformWechat() {
        return $this->belongsTo(PlatformWechat::class, 'platform_wechat_id', 'id');
    }

}