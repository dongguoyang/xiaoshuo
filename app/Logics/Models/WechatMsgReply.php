<?php

namespace App\Logics\Models;

class WechatMsgReply extends Base
{
    protected $table = 'wechat_msg_replies';
    //
    protected $fillable = ['customer_id', 'platform_wechat_id', 'keyword', 'reply_content'];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'keyword', 'reply_content'
    ];

    public function platformWechat() {
        return $this->hasMany(PlatformWechat::class, 'platform_wechat_id', 'id');
    }

}