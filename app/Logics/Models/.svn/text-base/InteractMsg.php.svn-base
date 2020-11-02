<?php

namespace App\Logics\Models;

class InteractMsg extends Base
{
    protected $table = 'interact_msgs';
    //
    protected $fillable = ['platform_wechat_id', 'name', 'type', 'content', 'send_to', 'send_type', 'send_at', 'status'];

    public $fields = [
        'id',
        'platform_wechat_id', 'name', 'type', 'content', 'send_to', 'send_type', 'send_at', 'status'
    ];

    public function platformWechat() {
        return $this->hasMany(PlatformWechat::class, 'platform_wechat_id', 'id');
    }

}