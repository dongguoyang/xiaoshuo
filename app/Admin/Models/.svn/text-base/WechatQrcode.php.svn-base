<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Builder;

class WechatQrcode extends Base
{
    //
    protected $fillable = [
        'customer_id', 'plat_wechat_id', 'novel_id', 'section', 'params', 'name', 'img', 'qrcode', 'status',
        'scan_num', 'sub_num', 'recharge_num', 'recharge_money', 'order_num', 'order_money', 'user_num',
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('customer_id', $customer->id);
            }
        });
    }

    public function platformWechat() {
        return $this->belongsTo(Wechat::class, 'plat_wechat_id', 'id');
    }
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }



    public function getParamsAttribute($params)
    {
        $rel = [];
        if (IsJson($params)) {
            $data = json_decode($params, 1);
            foreach ($data as $k=>$v) {
                $rel[] = ['key' => $k, 'value' => $v];
            }
        }
        return $rel;
    }

    public function setParamsAttribute($params)
    {
        if (is_array($params)) {
            $data = [];
            foreach ($params as $item) {
                $data[$item['key']] = $item['value'];
            }
            $params = json_encode($data);
        }
        $this->attributes['params'] = $params;
    }

    public function getUserNumAttribute($params)
    {
        $rel['user_num'] = $params;
        $rel['scan_num'] = $this->attributes['scan_num'];
        $rel['sub_num'] = $this->attributes['sub_num'];
        $rel['recharge_num'] = $this->attributes['recharge_num'];
        $rel['recharge_money'] = bcdiv($this->attributes['recharge_money'], 100, 2);
        $rel['order_num'] = $this->attributes['order_num'];
        $rel['order_money'] = bcdiv($this->attributes['order_money'], 100, 2);
        $rel['cost']    = bcdiv($this->attributes['cost'], 100, 2);

        $rel['rechargesucc2sub'] = floatval($rel['sub_num'])>0 ? bcdiv(($rel['recharge_num'] * 100), $rel['sub_num'], 2) . '%' : '无数据'; //充值成功/关注比例
        $rel['recharge2sub'] = floatval($rel['sub_num'])>0 ? bcdiv(($rel['order_num'] * 100), $rel['sub_num'], 2) . '%' : '无数据';//充值/关注比
        $rel['rechargesucc2cost'] = floatval($rel['cost'])>0 ? bcdiv($this->attributes['recharge_money'], $rel['cost'], 2) . '%' : '无数据';//回本率
        $rel['recharge2succ'] = floatval($rel['order_num'])>0 ? bcdiv(($rel['recharge_num'] * 100), $rel['order_num'], 2) . '%' : '无数据';//充值成功率

        return $rel;
    }
    public function getRechargeNumAttribute($params)
    {
        $rel['user_num'] = $this->attributes['user_num'];
        $rel['scan_num'] = $this->attributes['scan_num'];
        $rel['sub_num'] = $this->attributes['sub_num'];
        $rel['recharge_num'] = $params;
        $rel['recharge_money'] = bcdiv($this->attributes['recharge_money'], 100, 2);
        $rel['order_num'] = $this->attributes['order_num'];
        $rel['order_money'] = bcdiv($this->attributes['order_money'], 100, 2);
        $rel['cost']    = bcdiv($this->attributes['cost'], 100, 2);

        $rel['rechargesucc2sub'] = floatval($rel['sub_num'])>0 ? bcdiv(($rel['recharge_num'] * 100), $rel['sub_num'], 2) . '%' : '无数据'; //充值成功/关注比例
        $rel['recharge2sub'] = floatval($rel['sub_num'])>0 ? bcdiv(($rel['order_num'] * 100), $rel['sub_num'], 2) . '%' : '无数据';//充值/关注比
        $rel['rechargesucc2cost'] = floatval($rel['cost'])>0 ? bcdiv($this->attributes['recharge_money'], $rel['cost'], 2) . '%' : '无数据';//回本率
        $rel['recharge2succ'] = floatval($rel['order_num'])>0 ? bcdiv(($rel['recharge_num'] * 100), $rel['order_num'], 2) . '%' : '无数据';//充值成功率

        return $rel;
    }
}