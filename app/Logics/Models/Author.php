<?php

namespace App\Logics\Models;

class Author extends Base
{
    protected $table = 'authors';
    //
    protected $fillable = ['name', 'status', ];

    public $fields = [
        'id',
        'name', 'status',
    ];

    public function novel() {
        return $this->hasMany(Novel::class, 'author_id', 'id');
    }

}