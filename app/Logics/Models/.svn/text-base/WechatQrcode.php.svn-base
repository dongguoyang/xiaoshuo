<?php

namespace App\Logics\Models;

class WechatQrcode extends Base
{
    protected $table = 'wechat_qrcodes';
    //
    protected $fillable = [
        'customer_id', 'plat_wechat_id', 'novel_id', 'section', 'params', 'name', 'img', 'qrcode', 'status',
        'scan_num', 'sub_num', 'recharge_num', 'recharge_money', 'order_num', 'order_money', 'user_num',
    ];

    public $fields = [
        'id',
        'customer_id', 'plat_wechat_id', 'novel_id', 'section', 'params', 'name', 'img', 'qrcode', 'status',
        'scan_num', 'sub_num', 'recharge_num', 'recharge_money', 'order_num', 'order_money', 'user_num',
    ];

    public function platformWechat() {
        return $this->hasMany(PlatformWechat::class, 'plat_wechat_id', 'id');
    }

}