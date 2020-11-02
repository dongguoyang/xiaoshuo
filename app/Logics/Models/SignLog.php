<?php

namespace App\Logics\Models;

class SignLog extends Base
{
    protected $table = 'sign_logs';

    protected $fillable = [
        'customer_id', 'platform_wechat_id', 'user_id', 'continue_day', 'coin',
    ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'user_id', 'continue_day', 'coin', 'created_at',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}