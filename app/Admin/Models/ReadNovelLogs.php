<?php
namespace App\Admin\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class ReadNovelLogs extends Base {
    protected $table = 'read_novel_logs';
    protected $fillable = ['customer_id', 'platform_wechat_id',  'novel_id', 'name', 'novel_section_id', 'user_read_num',  'title','updated_at', 'created_at'];
    public $fields = [ 'id', 'customer_id', 'platform_wechat_id', 'novel_id', 'name', 'novel_section_id', 'user_read_num',  'title', 'updated_at', 'created_at'];


    public function novel() {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

}