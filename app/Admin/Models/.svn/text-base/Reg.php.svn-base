<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Reg extends Base
{
    //
    protected $table='reg';
    public function regType(){
       return $this->hasMany(RegType::class,'reg_id','id');
   }

}
