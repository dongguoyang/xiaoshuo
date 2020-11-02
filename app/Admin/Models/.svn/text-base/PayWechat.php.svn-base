<?php

namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class PayWechat extends Base
{
    protected $table = 'pay_wechats';
    protected $fillable = [
        'name', 'appid', 'appsecret', 'token', 'token_out', 'mch_id', 'mch_secret', 'mch_pem_dir', 'status', 'img', 'redirect_uri', 'type', 'up_num', 'bak_host',
    ];
    public $fields = [
        'id',
        'name', 'appid', 'appsecret', 'token', 'token_out', 'mch_id', 'mch_secret', 'status', 'img', 'redirect_uri', 'type', 'up_num', 'bak_host',
    ];

    public function getMchPemDirAttribute($value) {
        if (IsJson($value)) {
            $value = json_decode($value, 1);
        }
        return $value;
    }

    public function setMchPemDirAttribute($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $this->attributes['mch_pem_dir'] = $value;
    }

    public function setRedirectUriAttribute($value) {
        if (strpos($value, 'http')===false) {
            $value = 'http://' . $value;
        }
        $this->attributes['redirect_uri'] = $value;
    }
    public function setBakHostAttribute($value) {
        if (strpos($value, 'http')===false) {
            $value = 'http://' . $value;
        }
        $this->attributes['bak_host'] = $value;
    }
    public function setImgAttribute($value) {
        if (strpos($value, 'http')===false) {
            // $value = CloudPreDomain() . $value;
            $value = Storage::disk(config('admin.upload.disk'))->url($value);
        }
        $this->attributes['img'] = $value;
    }
}