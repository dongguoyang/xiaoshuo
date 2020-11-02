<?php

namespace App\Logics\Models;

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


}