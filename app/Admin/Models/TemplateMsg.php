<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Builder;

class TemplateMsg extends Base
{
    protected $table = 'template_msgs';

    protected $fillable = [
        'customer_id', 'platform_wechat_id', 'title', 'template_id', 'content', 'type', 'status',
    ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'title', 'template_id', 'content', 'type', 'status',
    ];

    public function getContentAttribute($content)
    {
        if(empty($content)){
            return $content;
        }

        $content = json_decode($content,1);

        foreach ($content as $content_key=>$content_v){
            $content[$content_key]['keyword']=$content_key;
        }
        unset($content_key);
        unset($content_v);

        return $content;
    }
    public function setContentAttribute($value){
        $data=[];
        foreach ($value as $value_key=>$value_v){
            $data[$value_v['keyword']]=$value_v;
            unset( $data[$value_v['keyword']]['keyword']);
        }
        $this->attributes['content'] = json_encode($data);
    }

    public function platformWechat() {
        return $this->belongsTo(PlatformWechat::class, 'platform_wechat_id', 'id');
    }
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }


    /**
     * 后台所需状态字段
     */
    public static function selectList($is_show = 0, $titles = [
        1 => '继续阅读提醒',
        2 => '首充优惠提醒',
        3 => '签到成功提醒',
        4 => '未支付提醒',
        5 => '推荐阅读提醒',
    ])
    {
        $colors = [
            'btn-warning',
            'btn-success',
            'btn-danger',
            'btn-primary',
            'btn-info',
            'btn-default',
            'btn-link',
        ];
        $states = [];
        $first = null;
        if ($is_show) {
            foreach ($titles as $k=>$v) {
                if ($first === null) $first = $k;
                $states[$k] = "<span class='btn btn-xs {$colors[abs(($k - $first) % count($colors))]}'>{$v}</span>";
            }
        } else {
            $states = $titles;
        }

        return $states;
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