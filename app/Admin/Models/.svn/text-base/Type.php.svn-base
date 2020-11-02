<?php

namespace App\Admin\Models;

use Illuminate\Support\Facades\Cache;

class Type extends Base {
	protected $fillable = ['pid', 'name', 'status', 'sort'];

	/**
	 * 获取文章分类对应的文章
	 */
	public function video() {
		return $this->belongsToMany(Video::class, 'video_type_rels', 'type_id', 'video_id');
	}

	public function pType() {
	    return $this->belongsTo(Type::class, 'pid', 'id');
    }

    public static function treeList() {
        $list = self::where('status', 1)->select(['id', 'pid', 'name'])->get()->toArray();
        $rel = $data = [];
        foreach ($list as $k=>$v) {
            if (!$v['pid']) {
                $data[$v['id']] = $v;
            } else {
                $data[$v['pid']]['child'][$v['id']] = $v;
            }
        }
        $rel[0] = '顶级';
        foreach ($data as $k=>$v) {
            $rel[$v['id']] = $v['name'];
            if (isset($v['child']) && $v['child']) {
                foreach ($v['child'] as $v2) {
                    $rel[$v2['id']] = '   ' . $v2['name'];
                }
            }
        }

        return $rel;
    }

    /**
     * 获取查询过滤的文章分类
     * @return mixed
     */
	/*public static function getArticleFilter()
    {
        $key = 'admin_article_filter_types';
        return Cache::rememberForever($key, function () {
           return self::where('status', 1)->pluck('name', 'id');
        });
    }*/
}
