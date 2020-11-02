<?php

namespace App\Logics\Models;

class Prize extends Base
{
    protected $table = 'prizes';

    protected $fillable = ['name', 'img', 'count', 'send_num', 'type', 'coin', 'coupon_id', 'chance', 'status',
        ];

    public $fields = [
        'id',
        'name', 'img', 'count', 'send_num', 'type', 'coin', 'coupon_id', 'chance', 'status',
    ];

    public function prizeLog() {
        return $this->hasMany(PrizeLog::class, 'prize_id', 'id');
    }

}