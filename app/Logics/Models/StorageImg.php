<?php

namespace App\Logics\Models;

class StorageImg extends Base
{
    protected $table = 'storage_imgs';

    protected $fillable = [
        'img', 'type', 'status',
    ];

    public $fields = [
        'id',
        'img', 'type',
    ];


}