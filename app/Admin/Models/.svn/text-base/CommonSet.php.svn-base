<?php

namespace App\Admin\Models;

class CommonSet extends Base {
    public $pageRow = 50;

    protected $fillable = ['type', 'name', 'value', 'value_type', 'title', 'status','sort'];

    public function getValueAttribute($value) {
        if ($this->value_type == 'json') {
            $value = json_decode($value, 1);
            if ($this->type == 'sign' && $this->name == 'coin_nums') {
                foreach ($value as $k=>$v) {
                    $data['第'.$k.'天'] = $v . ' 书币';
                }
                $value = $data;
            }
        }
        return $value;
    }

    public function setValueAttribute($value) {
        if ($this->value_type == 'json') {
            if ($this->type == 'sign' && $this->name == 'coin_nums') {
                $i = 1;
                foreach ($value['values'] as $v) {
                    $data[$i] = intval($v);
                    $i++;
                }
            } else {
                foreach ($value['keys'] as $k=>$v) {
                    $data[$v] = $value[$value['values'][$k]];
                }
            }
            $value = json_encode($data);
        }
        $this->attributes['value'] = $value;
    }

}