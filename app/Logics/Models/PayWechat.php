<?php

namespace App\Logics\Models;

class PayWechat extends Base
{
    protected $table = 'pay_wechats';

    protected $fillable = [
            'name', 'appid', 'appsecret', 'token', 'token_out', 'mch_id', 'mch_secret', 'status', 'img', 'redirect_uri', 'type', 'up_num','customer_id','money_today','money_max',
    ];

    public $fields = [
        'id',
        'name', 'appid', 'appsecret', 'token', 'token_out', 'mch_id', 'mch_secret', 'status', 'img', 'redirect_uri', 'type', 'up_num','customer_id','money_today','money_max',
    ];

}