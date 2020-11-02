<?php

namespace App\Admin\Models;

class DomainType extends Base
{
    //
    protected $fillable = ['name', 'status', 'rand_num', 'pre', 'ip_num', 'time_len', 'no', 'view_num'];

    public function domain() {
        return $this->hasMany(Domain::class, 'type_id', 'id');
    }

}