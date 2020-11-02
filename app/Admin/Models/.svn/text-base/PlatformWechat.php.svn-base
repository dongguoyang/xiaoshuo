<?php

namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class PlatformWechat extends Base
{
    protected $table = 'platform_wechats';
    protected $fillable = [
        'customer_id', 'platform_id', 'component_appid', 'appid', 'app_secret', 'mch_id', 'mch_secret', 'mch_pem_dir', 'js_api_ticket_out_time', 'js_api_ticket', 'origin_id', 'app_name', 'service_type', 'img',
        'auth_time', 'auth_out_time', 'token', 'token_out', 'refresh_token', 'refresh_out', 'verify_ticket', 'type', 'status', 'domain', 'domain_status',
        'msg', 'menu', 'web', 'notice', 'user', 'source', 'shop', 'card', 'store', 'scan', 'wifi', 'shake', 'city', 'ad', 'receipt', 'reg_miniapp', 'man_miniapp', 'calorie',
        'account', 'kefu', 'open_account', 'plugin', 'addr', 'develop', 'updated_at', 'created_at'
    ];
    public $fields = [
        'id', 'customer_id', 'platform_id', 'component_appid', 'appid', 'app_secret', 'mch_id', 'mch_secret', 'mch_pem_dir', 'js_api_ticket_out_time', 'js_api_ticket', 'origin_id', 'app_name', 'service_type', 'img',
        'auth_time', 'auth_out_time', 'token', 'token_out', 'refresh_token', 'refresh_out', 'verify_ticket', 'type', 'status', 'domain', 'domain_status',
        'msg', 'menu', 'web', 'notice', 'user', 'source', 'shop', 'card', 'store', 'scan', 'wifi', 'shake', 'city', 'ad', 'receipt', 'reg_miniapp', 'man_miniapp', 'calorie',
        'account', 'kefu', 'open_account', 'plugin', 'addr', 'develop', 'updated_at', 'created_at'
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('customer_id', '=', $customer->id);
            }
        });
    }

    public function platform() {
        return $this->belongsTo(Platform::class, 'platform_id', 'id');
    }
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function getAuthTimeAttribute($value) {
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
    public function getTokenOutAttribute($value) {
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
    public function getRefreshOutAttribute($value) {
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
    // js_api_ticket_out_time
    public function getJsApiTicketOutTimeAttribute($value) {
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
    public function getMchPemDirAttribute($value) {
        if (IsJson($value)) {
            $value = json_decode($value, 1);
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
}