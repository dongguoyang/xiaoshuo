<?php

namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class WechatMsgReply extends Base
{
    protected $table = 'wechat_config';
    protected $fillable = [
        'customer_id', 'platform_wechat_id', 'keyword', 'reply_content',
    ];
    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'keyword', 'reply_content',
    ];

    public function platformWechat() {
        return $this->belongsTo(PlatformWechat::class, 'platform_wechat_id', 'id');
    }
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
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
}