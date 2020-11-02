<?php
namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class CoinLog extends Base {
    protected $table = 'coin_logs';
    protected $fillable = ['customer_id', 'platform_wechat_id', 'user_id', 'type', 'type_id', 'coin', 'balance', 'title', 'desc', 'status', 'updated_at', 'created_at'];
    public $fields = ['id', 'customer_id', 'platform_wechat_id', 'user_id', 'type', 'type_id', 'coin', 'balance', 'title', 'desc', 'status', 'updated_at', 'created_at'];
    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = Admin::user();
            if(!$customer->isAdministrator()) {
                $builder->where('customer_id', '=', $customer->id);
            } else {
                $builder->where('id', '>', 0);
            }
        });
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}