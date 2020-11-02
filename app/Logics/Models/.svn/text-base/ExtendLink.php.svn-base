<?php

namespace App\Logics\Models;

class ExtendLink extends Base
{
    protected $table = 'extend_links';
    //
    protected $fillable = ['customer_id', 'novel_id', 'novel_section_num', 'title', 'cost', 'link', 'type', 'must_subscribe', 'subscribe_section', 'status', 'page_conf', 'data_info',  ];

    public $fields = [
        'id',
        'customer_id', 'novel_id', 'novel_section_num', 'title', 'cost', 'link', 'type', 'must_subscribe', 'subscribe_section', 'status', 'page_conf', 'data_info',
        'updated_at',
    ];

    public function customer() {
        return $this->hasMany(Customer::class, 'customer_id', 'id');
    }

    public function novelSection() {
        return $this->hasMany(NovelSection::class, 'novel_section_id', 'id');
    }

    public function novel() {
        return $this->hasMany(Novel::class, 'novel_id', 'id');
    }

}