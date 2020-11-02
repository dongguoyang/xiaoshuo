<?php
namespace App\Admin\Models;

class Material extends Base {
    protected $table = 'materials';
    protected $fillable = ['type', 'img', 'name', 'status', 'updated_at', 'created_at'];
    public $fields = ['id', 'type', 'img', 'name', 'status', 'updated_at', 'created_at'];
}