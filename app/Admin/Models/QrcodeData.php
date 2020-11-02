<?php

namespace App\Admin\Models;

class QrcodeData extends Base {
    protected $table = 'qrcode_datas';
    protected $fillable = ['name', 'wechat_qrcode_ids', 'recharge_num', 'recharge_succ_num', 'recharge_money', 'recharge_succ_money', 'cost', 'user_num', 'scan_num', 'sub_num',
        'start_at', 'end_at',
    ];
    public $fields = ['id', 'name', 'wechat_qrcode_ids', 'recharge_num', 'recharge_succ_num', 'recharge_money', 'recharge_succ_money', 'cost', 'user_num', 'scan_num', 'sub_num',
        'start_at', 'end_at',
    ];

    protected $dates = ['created_at', 'updated_at' , 'start_at', 'end_at']; // 自动将里面的字段改成datetime格式

    protected $casts = ['created_at' => 'date:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s','start_at' => 'date:Y-m-d H:i:s', 'end_at' => 'datetime:Y-m-d H:i:s',];

    public function getWechatQrcodeIdsAttribute($value)
    {
        return explode(',', $value);
    }

    public function setWechatQrcodeIdsAttribute($value)
    {
        $this->attributes['wechat_qrcode_ids'] = implode(',', $value);
    }

}