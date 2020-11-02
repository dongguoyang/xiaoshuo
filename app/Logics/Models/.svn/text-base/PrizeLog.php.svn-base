<?php

namespace App\Logics\Models;

class PrizeLog extends Base
{
    protected $table = 'prize_logs';

    protected $fillable = ['customer_id', 'platform_wechat_id', 'prize_id', 'user_id', 'username', 'prize_name', 'desc', 'status',
        ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'prize_id', 'user_id', 'username', 'prize_name', 'desc', 'status',
    ];

    public function prize() {
        return $this->belongsTo(Prize::class, 'prize_id', 'id');
    }

}