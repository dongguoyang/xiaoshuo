<?php

namespace App\Logics\Models;

class NovelSection extends Base
{
    protected $table = 'novel_sections';

    protected $fillable = ['novel_id', 'num', 'title', 'content', 'updated_num', 'spider_url'
        ];

    public $fields = [
        'id',
        'novel_id', 'num', 'title', 'content', 'updated_num',
    ];

    public function novel() {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

}