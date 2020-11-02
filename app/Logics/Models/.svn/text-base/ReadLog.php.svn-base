<?php

namespace App\Logics\Models;

class ReadLog extends Base
{
    protected $table = 'read_logs';

    protected $fillable = [
        'customer_id', 'platform_wechat_id', 'user_id', 'novel_id', 'name', 'novel_section_id', 'end_section_num', 'max_section_num', 'title', 'status', 'sectionlist', 'view_cid',
    ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'user_id', 'novel_id', 'name', 'novel_section_id', 'end_section_num', 'max_section_num', 'title', 'status', 'sectionlist', 'view_cid',
    ];

    public function novel() {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}