<?php

namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Wechat extends Base
{
    protected $table = 'wechats';
    protected $fillable = [
        'customer_id', 'name', 'appid', 'appsecret', 'token', 'token_out', 'service_token', 'service_aes_key', 'status', 'img', 'redirect_uri', 'type', 'updated_at', 'created_at', 'bak_host',
    ];
    public $fields = [
        'id',
        'customer_id', 'name', 'appid', 'appsecret', 'token', 'token_out', 'service_token', 'service_aes_key', 'status', 'img', 'redirect_uri', 'type', 'updated_at', 'created_at', 'bak_host',
    ];

    public function getMchPemDirAttribute($value) {
        if (IsJson($value)) {
            $value = json_decode($value, 1);
        }
        return $value;
    }

    public function getTokenOutAttribute($value) {
        if (is_numeric($value)) {
            $value = date('Y-m-d H:i:s', $value);
        }
        return $value;
    }

    public function setImgAttribute($value) {
        if (strpos($value, 'http')===false) {
            // $value = CloudPreDomain() . $value;
            $value = Storage::disk(config('admin.upload.disk'))->url($value);
        }
        $this->attributes['img'] = $value;
    }
    public function setRedirectUriAttribute($value) {
        if (strpos($value, 'http')===false) {
            $value = 'http://' . $value;
        }
        $this->attributes['redirect_uri'] = $value;
    }
    public function setBakHostAttribute($value) {
        if (!empty($value) && strpos($value, 'http')===false) {
            $value = 'http://' . $value;
        }
        $this->attributes['bak_host'] = $value;
    }
    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('customer_id', '=', $customer->id);
            }
        });
    }
    
    public function get_fans_number($id){
        $wechat_data = $this::find($id);
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='. $wechat_data['token'];// .'&next_openid='. $next_openid;
        $rel = @file_get_contents($url);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            return 0;
        }
        return $rel['total'];
    }
}