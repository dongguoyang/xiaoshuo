<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
class RegNullData extends Model
{
    //
    protected $table="reg_null_data";
    public function novels(){
        return $this->hasOne(\App\Admin\Models\Novel::class,'id','novel_id');
    }
    public function reg(){
        return  $this->hasOne(Reg::class,'id','reg_id');
    }

}
