<?php

namespace App\Admin\Models;

class CheckDomain extends Base
{
    //
    protected $fillable = ['host', 'status'];
    public $timestamps = false;
    protected $table = 'check_domains';

}