<?php

namespace App\Logics\Models;

class Domain extends Base
{
    protected $table = 'domains';

    protected $fillable = ['host', 'app', 'status', 'type_id', 'ip_num', 'view_num', ];

    public $fields = [
        'id',
        'host', 'app', 'status', 'type_id', 'ip_num', 'view_num',
    ];

}