<?php

namespace App\Logics\Models;

class UserReadDay extends Base {
    protected $table = 'user_read_day';
    protected $fillable = ['date_belongto', 'group_id', 'customer_id', 'user_id' , 'read_num', 'novel_id', 'updated_at', 'created_at'];
    public $fields = ['id', 'date_belongto', 'group_id', 'customer_id', 'user_id' , 'read_num', 'novel_id', 'updated_at', 'created_at'];
}