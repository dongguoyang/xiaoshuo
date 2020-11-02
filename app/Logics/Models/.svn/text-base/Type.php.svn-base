<?php

namespace App\Logics\Models;

class Type extends Base
{
    protected $table = 'types';

    protected $fillable = [
        'pid', 'name', 'status', 'sort', 'level',
    ];

    public $fields = [
        'id',
        'pid', 'name', 'status', 'sort', 'level',
    ];


    public function novel() {
        return $this->hasMany(Novel::class, 'type_id', 'id');
    }

    public function PType() {
        return $this->belongsTo(Type::class, 'pid', 'id');
    }

    public function CType() {
        return $this->hasMany(Type::class, 'pid', 'id');
    }


}