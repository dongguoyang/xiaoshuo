<?php

namespace App\Logics\Models;

class CoinLog extends Base
{
    protected $table = 'coin_logs';

    protected $fillable = ['customer_id', 'platform_wechat_id', 'user_id', 'type', 'type_id', 'coin', 'balance', 'title', 'desc', 'status'];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'user_id', 'type', 'type_id', 'coin', 'balance', 'title', 'desc', 'status'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}