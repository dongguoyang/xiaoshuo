<?php
namespace App\Logics\Models;


class CoinAct extends Base {
    protected $table = 'coin_acts';
    protected $fillable = ['name', 'coin', 'start_at', 'end_at', 'limit', 'customer_id', 'status'];
    public $fields = ['id', 'name', 'coin', 'start_at', 'end_at', 'limit', 'customer_id', 'status'];

}