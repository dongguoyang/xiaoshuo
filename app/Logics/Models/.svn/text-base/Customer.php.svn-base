<?php

namespace App\Logics\Models;

class Customer extends Base
{
    protected $table = 'customers';

    protected $fillable = ['pid', 'username', 'password', 'name', 'avatar', 'remember_token', 'tel', 'qq', 'wexin', 'truename',
        'company', 'addr', 'email', 'status', 'web_info', 'web_tpl', 'balance', 'recharge_money', 'cash_money', 'cashing_num',
        'cashed_num', 'bank_name', 'bank_info', 'bank_no',
        ];

    public $fields = [
        'id',
        'pid', 'username', 'password', 'name', 'avatar', 'remember_token', 'tel', 'qq', 'wexin', 'truename',
        'company', 'addr', 'email', 'status', 'web_info', 'web_tpl', 'balance', 'recharge_money', 'cash_money', 'cashing_num',
        'cashed_num', 'bank_name', 'bank_info', 'bank_no',
    ];

    public function PCustomer() {
        return $this->belongsTo(Customer::class, 'pid', 'id');
    }

    public function CCustomer() {
        return $this->hasMany(Customer::class, 'pid', 'id');
    }

    public function customerMoneyLogs() {
        return $this->hasMany(CustomerMoneyLog::class, 'customer_id', 'id');
    }

}