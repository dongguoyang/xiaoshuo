<?php

namespace App\Logics\Models;

class PlatformWechat extends Base
{
    protected $table = 'platform_wechats';

    public $fillable = ['customer_id', 'platform_id', 'component_appid', 'appid', 'app_secret', 'mch_id', 'mch_secret', 'mch_pem_dir', 'js_api_ticket_out_time', 'js_api_ticket',
        'origin_id', 'app_name', 'service_type', 'img', 'auth_time', 'auth_out_time', 'token',  'token_out', 'refresh_token', 'refresh_out',
        'verify_ticket', 'type', 'status', 'domain', 'domain_status', 'msg', 'menu', 'web',
        'notice', 'user', 'source', 'shop', 'card', 'store', 'scan', 'wifi', 'shake', 'city', 'ad',
        'receipt', 'reg_miniapp', 'man_miniapp', 'calorie', 'account', 'kefu', 'open_account', 'plugin', 'addr', 'develop',
        ];

    public $fields = [
        'id',
        'customer_id', 'platform_id', 'component_appid', 'appid', 'app_secret', 'mch_id', 'mch_secret', 'mch_pem_dir', 'js_api_ticket_out_time', 'js_api_ticket',
        'origin_id', 'app_name', 'service_type', 'img', 'auth_time', 'auth_out_time', 'token',  'token_out', 'refresh_token', 'refresh_out',
        'verify_ticket', 'type', 'status', 'domain', 'domain_status', 'msg', 'menu', 'web',
        'notice', 'user', 'source', 'shop', 'card', 'store', 'scan', 'wifi', 'shake', 'city', 'ad',
        'receipt', 'reg_miniapp', 'man_miniapp', 'calorie', 'account', 'kefu', 'open_account', 'plugin', 'addr', 'develop',
    ];

    public function platform() {
        return $this->belongsTo(Platform::class, 'platform_id', 'id');
    }

}