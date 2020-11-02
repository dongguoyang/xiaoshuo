<?php

namespace App\Admin\Models;

class AdPosition extends Base
{
    //
    protected $fillable = ['name', 'remark', 'page', 'status', 'ad_id', 'code', ];

    public function ad() {
        // return $this->belongsTo(Ad::class, 'ad_id', 'id');
        return $this->hasOne(Ad::class, 'id', 'ad_id');
    }

    //获取广告位显示页面
    public static function getPage($show = 0) {
        if ($show) {
            return [
                'shared' => '<button type="button" class="btn btn-info btn-xs">文章分享页</button>',
                'adlist' => '<button type="button" class="btn btn-warning btn-xs">广告列表页</button>',
                'mycenter' => '<button type="button" class="btn btn-success btn-xs">个人中心页</button>',
                'adpage' => '<button type="button" class="btn btn-primary btn-xs">广告页</button>',
            ];
        } else {
            return [
                'shared' => '文章分享页',
                'adlist' => '广告列表页',
                'mycenter' => '个人中心页',
                'adpage' => '广告页',
            ];
        }
    }
}