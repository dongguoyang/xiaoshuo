<?php

namespace App\Logics\Models;

class StorageTitle extends Base
{
    protected $table = 'storage_titles';

    protected $fillable = [
        'title', 'desc', 'type', 'status',
    ];

    public $fields = [
        'id',
        'title', 'desc', 'type',
    ];


}