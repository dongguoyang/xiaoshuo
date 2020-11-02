<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Comment;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class CommentRepository extends Repository {
	public function model() {
		return Comment::class;
	}
    /**
     * 获取评论记录
     * @param int $novel_id
     * @param int $page
     */
    public function CommentList($novel_id, $page) {
        $key = config('app.name'). 'comment_list_'.$novel_id.'_'.$page;

        return Cache::remember($key, 60, function () use ($novel_id, $page) {
            $start = $this->pagenum * ($page - 1);

            $list = $this->model
                ->where('novel_id', $novel_id)
                ->offset($start)
                ->limit($this->pagenum)
                ->select($this->model->fields)
                ->orderBy('id', 'desc')
                ->get();

            return $this->toArr($list);
        });
    }
    /**
     * 清除书评缓存
     */
    public function ClearCache($novel_id) {
        $page = 1;
        $key = config('app.name'). 'comment_list_'.$novel_id.'_';
        while ($page < 15) {
            if (!Cache::has($key . $page)) {
                break;
            }
            Cache::forget($key . $page);
            $page++;
        }
    }
}