<?php
namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class PageLog extends Base {
    protected $table = 'page_logs';
    protected $fillable = ['bl_date', 'recharge_start', 'recharge_end', 'section_null', 'unsub_user',];
    public $fields = ['id', 'bl_date', 'recharge_start', 'recharge_end', 'section_null', 'unsub_user',];


}