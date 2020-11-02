<?php

namespace App\Logics\Models;

use Illuminate\Database\Eloquent\Model;

class Base extends Model {

    protected $table = 'ads'; // 表名

	protected $dateFormat = 'U'; // created_at updated_at 等时间日期格式使用时间戳保存

    public $pageRows = 20; // 每页分页数据条数

	protected $casts = [
		'created_at' => 'timestamp',
		'updated_at' => 'timestamp',
	];

    protected $fillable = [];

    public $fields = []; // 常用的查询列表


    /*
    public function getIdAttribute($value) {
		if (strlen($value) > 10) {
			return (string) $value;
		}
		return $value;
	}*/
}
