<?php

namespace App\Admin\Models;

class Ad extends Base
{
    protected $fillable = ['title', 'desc', 'img', 'url', 'status', 'view_num', 'click_num', 'type', 'customer', 'sort', ];

    public function adPosition() {
        return $this->belongsTo(AdPosition::class, 'ad_id', 'id');
    }

    public function setImgAttribute($value) {
        if (strpos($value, 'http')===false) {
            $value = CloudPreDomain() . $value;
        }
        $this->attributes['img'] = $value;
    }

    //状态
    public static function getType($show = 0) {
        if ($show) {
            return [
                1 => '<button type="button" class="btn btn-info btn-xs">benner图</button>',
                2 => '<button type="button" class="btn btn-success btn-xs">左右图文</button>',
                3 => '<button type="button" class="btn btn-warning btn-xs">上下图文</button>',
            ];
        } else {
            return [
                1 => 'benner图',
                2 => '左右图文',
                3 => '上下图文',
            ];
        }
    }
}