<?php

namespace App\Logics\Models;

class WechatConfig extends Base
{
    protected $table = 'wechat_config';
    //
    protected $fillable = [
        'customer_id', 'platform_wechat_id', 'subscribe_msg', 'subscribe_content', 'subscribe_msg_next', 'subscribe_msg_12h', 'menu_list', 'continue_read_tip', 'unpay_tip', 'frist_recharge_tip',
        'sign_tip', 'pushconf', 'daily_push', 'search_sub', 'user_tags',
    ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'subscribe_msg', 'subscribe_content', 'subscribe_msg_next', 'menu_list', 'continue_read_tip', 'unpay_tip', 'frist_recharge_tip', 'sign_tip',
        'pushconf', 'daily_push', 'search_sub', 'user_tags',
    ];

    public function platformWechat() {
        return $this->hasMany(PlatformWechat::class, 'platform_wechat_id', 'id');
    }

}