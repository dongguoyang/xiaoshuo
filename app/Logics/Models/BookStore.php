<?php

namespace App\Logics\Models;

class BookStore extends Base
{
    protected $table = 'book_stores';

    protected $fillable = ['user_id', 'novel_id', 'novel_title', 'novel_img', 'section_num', 'status', ];

    public $fields = [
        'id',
        'user_id', 'novel_id', 'novel_title', 'novel_img', 'section_num', 'status',
    ];

}