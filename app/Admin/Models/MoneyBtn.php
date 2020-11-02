<?php

namespace App\Admin\Models;

class MoneyBtn extends Base {
    protected $table = 'money_btns';
    protected $fillable = ['price', 'reduction', 'coin', 'title', 'desc', 'default', 'status', 'tag', 'red_desc', 'updated_at', 'created_at','start_at','end_at'];
    public $fields = ['id', 'price', 'reduction', 'coin', 'title', 'desc', 'default', 'status', 'tag', 'red_desc', 'updated_at', 'created_at','start_at','end_at'];


    /**
     * 后台所需状态字段
     */
    public static function selectList($is_show = 0, $titles = ['关闭', '开启'])
    {
        $colors = [
            'btn-warning',
            'btn-success',
            'btn-danger',
            'btn-primary',
            'btn-info',
            'btn-default',
            //'btn-link',
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
    
    public function getStartAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {} else {
            $value = date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
    
    public function getEndAtAttribute($value) {
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
}