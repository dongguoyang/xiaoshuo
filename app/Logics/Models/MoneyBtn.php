<?php

namespace App\Logics\Models;

class MoneyBtn extends Base
{
    protected $table = 'money_btns';

    protected $fillable = ['price', 'reduction', 'coin', 'title', 'desc', 'default', 'status', 'tag', 'red_desc', 'updated_at', 'created_at'];

    public $fields = [
        'id',
        'price', 'reduction', 'coin', 'title', 'desc', 'default', 'status', 'tag', 'red_desc', 'updated_at', 'created_at', 'start_at' ,'end_at'
    ];
    
    
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

}