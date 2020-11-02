<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    const PAGE_NUM = 15;
    // 状态信息常量（常用值）；0 停用；1 正常；2 已删除
    const STATUS_0 = 0;
    const STATUS_1 = 1;
    const STATUS_2 = 2;
    // protected $connection = '';
    protected $fillable = []; // 可添加字段

    protected $pageRows = 25;
    protected $cloudColumn = [];

    protected $dates = ['created_at', 'updated_at' /*, 'start_at', 'end_at'*/]; // 自动将里面的字段改成datetime格式

    protected $casts = ['created_at' => 'date:Y-m-d', 'updated_at' => 'datetime:Y-m-d H:i:s',];
    // public $timestamps = false;	// 不自动维护 updated_at、created_at
    // protected $table = 'my_table_name'; // 表名

    /**
     * @param \DateTime|int $value
     * @return false|int
     * @author dividez
     * 这样修改以后 laravel 依然会自动维护 created_at 和 updated_at
     * 在我们取出 created_at 这个字段依然会为我们转换为 Carbon 类型
     * ==========================================
     * protected $dateFormat = 'U'; 与该方法不兼容
     * ==========================================
     */
    public function fromDateTime($value)
    {
        return strtotime(parent::fromDateTime($value));
    }

    /**
     * 后台所需状态字段
     */
    public static function switchStatus($is_show = 0, $titles = ['关闭', '开启'])
    {
        if ($is_show) {
            $states = [self::STATUS_0 => $titles[self::STATUS_0], self::STATUS_1 => $titles[self::STATUS_1],];
        } else {
            $states = ['off' => ['value' => self::STATUS_0, 'text' =>$titles[self::STATUS_0], 'color' => 'danger'], 'on' => ['value' => self::STATUS_1, 'text' => $titles[self::STATUS_1], 'color' => 'success'],];
        }

        return $states;
    }

    /**
     * 后台所需状态字段
     */
    public static function selectList($is_show = 0, $titles = ['关闭', '开启'])
    {
        $colors = [
            'btn-warning',
            'btn-success',
            'btn-danger',
            'btn-primary',
            'btn-info',
            'btn-default',
            'btn-link',
        ];
        $states = [];
        $first = null;
        if ($is_show) {
            foreach ($titles as $k=>$v) {
                if ($first === null) $first = $k;
                $states[$k] = "<span class='btn btn-xs {$colors[abs(($k - $first) % count($colors))]}'>{$v}</span>";
            }
        } else {
            $states = $titles;
        }

        return $states;
    }
    /**
     * 设置图片保存信息
     * 不建议使用该方法；使用之后阿里云的旧图片不能删除；destroy() 方法调用失败
     */
    /*public function setImgAttribute($value)
            {
                if (!starts_with($value, 'http')){
                    $value = config('filesystems.disks.oss.ssl') ? 'https://' : 'http://' . config('filesystems.disks.oss.bucket') . config('filesystems.disks.oss.endpoint') . '/' . $value;
                }
                $this->attributes['img'] = $value;
    */

    /**
     * 获取最后执行的sql 语句的方法
     * 1、开启日志记录
     * 2、获取日志
     * 3、返回日志信息
     */
    public function sql()
    {
        DB::connection($this->connection)->enableQueryLog();

        $sql = DB::getQueryLog();

        return $sql;
    }

    /*
    // 定义 修改器；修改 pub_at 字段保存值
    public function setPubAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {
            $value = strtotime($value);
        }
        $this->attributes['pub_at'] = $value;
    }
    // 定义 访问器；修改 pub_at 字段获取值
    public function getPubAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {} else {
            $value = date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
    */
}
