<?php

namespace App\Admin\Models;


use Illuminate\Support\Facades\Hash;

class GroupManager extends Base
{
    // 组员管理
    protected $table="customers";
    public function setPasswordAttribute($password) {
        if(!empty($password)) {
            $this->attributes['password'] = Hash::make($password);
        } else {
            unset($this->attributes['password']);
        }
    }
    
    public function getPidUser(){
        $result = $this->where('pid',1)->select(['id','name'])->get()->toArr();
        $data = $result->toArray();
        return $data;
    }
}
