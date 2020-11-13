<?php

namespace App\Logics\Models;

class ReadNovelLogs extends Base
{
    protected $table = 'read_novel_logs';

    protected $fillable = [
        'customer_id', 'platform_wechat_id',  'novel_id', 'name', 'novel_section_id', 'user_read_num',  'title',
    ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'novel_id', 'name', 'novel_section_id', 'user_read_num',  'title',
    ];

    public function novel() {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

}