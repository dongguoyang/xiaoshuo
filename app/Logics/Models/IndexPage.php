<?php

namespace App\Logics\Models;

class IndexPage extends Base
{
    protected $table = 'index_pages';

    protected $fillable = [
        'customer_id', 'platform_wechat_id', 'suitable_sex', 'type', 'novel_id', 'img'
    ];

    public $fields = [
        'id',
        'customer_id', 'platform_wechat_id', 'suitable_sex', 'type', 'novel_id', 'img'
    ];

    public static $type_s = [
        1 => '首页 顶部轮播图',
        2 => '首页 本周热门',
        3 => '首页 主编力荐',
        4 => '首页 本周新书',
        5 => '首页 出版物',
        6 => '首页 男女生精选',
        7 => '搜索页推荐（畅销书单）',
        8 => '搜索页推荐（大家都在搜）',
        9 => '书架推荐',
        10 => '原创榜推荐',
    ];

}