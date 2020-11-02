<?php

namespace App\Admin\Models;

class Platform extends Base
{
    //
    protected $fillable = [
        /*    'aaa', 'aaa', 'aaa', 'aaa', 'aaa', 'aaa', 'aaa', 'aaa', 'aaa', 'aaa',   */
        'name', 'img', 'appid', 'appsecret', 'auth_domain', 'auth_url', 'event_url', 'webauth_url', 'token', 'token_key',
        'status', 'is_pub', 'component_verify_ticket', 'authorizer_refresh_token', 'component_access_token', 'token_out_time',
    ];


    public function platformWechat() {
        return $this->hasMany(PlatformWechat::class, 'platform_id', 'id');
    }

    public function getTokenOutTimeAttribute($value) {
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
}