<?php

namespace App\Logics\Models;

class DomainCheck extends Base
{
    protected $table = 'domain_checks';

    protected $fillable = ['host', 'c_id', 'status', 'api', ];

    public $fields = [
        'id',
        'host', 'c_id', 'status', 'api',
    ];

}