<?php

namespace App\Logics\Models;

class RewardLog extends Base
{
    protected $table = 'reward_logs';

    protected $fillable = [
        'customer_id', 'platform_wechat_id', 'novel_id', 'user_id', 'username', 'user_img', 'goods_id', 'goods_num', 'goods_img', 'coin_num',
    ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'novel_id', 'user_id', 'username', 'user_img', 'goods_id', 'goods_num', 'goods_img', 'coin_num', 'created_at',
    ];

    public function goods() {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }


}