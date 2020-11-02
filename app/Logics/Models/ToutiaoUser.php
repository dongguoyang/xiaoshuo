<?php

namespace App\Logics\Models;

class ToutiaoUser extends Base
{
    protected $table = 'toutiao_user';

    protected $fillable = ['ip', 'user_agent', 'url', 'type','machine_type','os','updated_at', 'created_at', ];

    public $fields = [
        'id',
        'ip','user_agent', 'url', 'type', 'machine_type','os','updated_at', 'created_at',
    ];

}