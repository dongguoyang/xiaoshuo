<?php

namespace App\Logics\Models;

class Material extends Base
{
    protected $table = 'materials';

    protected $fillable = ['type', 'img', 'name', 'status'];

    public $fields = [
        'id',
        'type', 'img', 'name', 'status'
    ];


}