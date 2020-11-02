<?php
namespace App\Admin\Models;

class NovelCheckLog extends Base
{
    protected $table = 'novel_check_log';
    protected $fillable = ['target_type', 'total_count', 'valid_count', 'invalid_count', 'fixed_count', 'started_time', 'finished_time', 'last_target_id', 'updated_at', 'created_at'];
    public $fields = ['id', 'target_type', 'total_count', 'valid_count', 'invalid_count', 'fixed_count', 'started_time', 'finished_time', 'last_target_id', 'updated_at', 'created_at'];
}