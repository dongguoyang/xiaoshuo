<?php
namespace App\Admin\Models;


use Illuminate\Database\Eloquent\Builder;

class CoinAct extends Base {
    protected $table = 'coin_acts';
    protected $fillable = ['name', 'coin', 'start_at', 'end_at', 'limit', 'customer_id', 'status'];
    public $fields = ['id', 'name', 'coin', 'start_at', 'end_at', 'limit', 'customer_id', 'status'];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('customer_id', $customer->id);
            }
        });
    }


    // 定义 访问器；修改 pub_at 字段获取值
    public function getStartAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {} else {
            $value = date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
    public function setStartAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {
            $value = strtotime($value);
        }
        $this->attributes['start_at'] = $value;
    }
    public function setEndAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {
            $value = strtotime($value);
        }
        $this->attributes['end_at'] = $value;
    }
    // 定义 访问器；修改 pub_at 字段获取值
    public function getEndAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {} else {
            $value = date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
}