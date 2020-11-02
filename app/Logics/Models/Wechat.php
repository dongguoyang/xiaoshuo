<?php

namespace App\Logics\Models;

class Wechat extends Base
{
    protected $table = 'wechats';

    protected $fillable = [
        'customer_id', 'name', 'appid', 'appsecret', 'token', 'token_out', 'status', 'img', 'redirect_uri', 'type', 'service_token', 'service_aes_key',
    ];

    public $fields = [
        'id',
        'customer_id', 'name', 'appid', 'appsecret', 'token', 'token_out', 'status', 'img', 'redirect_uri', 'service_token', 'service_aes_key',
    ];

}