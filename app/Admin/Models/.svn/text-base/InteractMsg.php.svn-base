<?php
namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class InteractMsg extends Base {
    protected $table = 'interact_msgs';
    protected $fillable = ['platform_wechat_id', 'name', 'type', 'content', 'send_to', 'send_type', 'send_at', 'end_at', 'total_success', 'total_failure', 'status', 'updated_at', 'created_at'];
    public $fields = ['id', 'platform_wechat_id', 'name', 'type', 'content', 'send_to', 'send_type', 'send_at', 'end_at', 'total_success', 'total_failure', 'status', 'updated_at', 'created_at'];
    protected $dates = ['created_at', 'updated_at', 'send_at', 'end_at'];
    protected $casts = ['created_at' => 'datetime:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s', 'send_at' => 'datetime:Y-m-d H:i:s', 'end_at' => 'datetime:Y-m-d H:i:s'];
    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            $wechat = Wechat::where('customer_id', $customer['id'])->select(['id', 'name'])->first();
            if($customer['pid']) {
                $builder->where('platform_wechat_id', '=', $wechat->id);
            }
        });
    }

    public function platformWechat() {
        return $this->belongsTo(PlatformWechat::class, 'platform_wechat_id', 'id')->withDefault();
    }
}