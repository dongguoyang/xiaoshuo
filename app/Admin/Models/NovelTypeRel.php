<?php
namespace App\Admin\Models;


class NovelTypeRel extends Base {
    protected $table = 'novel_type_rels';
    protected $fillable = [
        'novel_id', 'type_id'
    ];
    public $fields = [
        'novel_id', 'type_id'
    ];
}