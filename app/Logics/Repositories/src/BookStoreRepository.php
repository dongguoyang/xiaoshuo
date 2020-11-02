<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\BookStore;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class BookStoreRepository extends Repository {
	public function model() {
		return BookStore::class;
	}
    /**
     * 获取用户书架列表
     */
	public function UserBooks($user_id, $page) {
	    $key = config('app.name') . 'book_store_list_'.$user_id.'_'.$page;
	    return Cache::remember($key, 1440, function () use ($user_id, $page) {
	        $offset = ($page - 1) * $this->pagenum;

	        $list = $this->model
                ->where('user_id', $user_id)
                ->where('status', 1)
                ->orderBy('updated_at', 'desc')
                ->offset($offset)
                ->limit($this->pagenum)
                ->select($this->model->fields)
                ->orderBy('updated_at', 'desc')
                ->get();
	        return $this->toArr($list);
        });
    }
    /**
     * 清除用户书架缓存
     * @param int $user_id
     * @param array|int $novel_ids
     */
    public function ClearCache($user_id, $novel_ids = []) {
        $key = config('app.name') . 'book_store_list_'.$user_id.'_';
        $page = 1;
        while ($page < 5) {
            if (!Cache::has($key . $page)) {
                break;
            }
            Cache::forget($key . $page);
            $page++;
        }

        if ($novel_ids) {
            if (is_array($novel_ids)) {
                foreach ($novel_ids as $novel_id) {
                    $key = config('app.name') . 'is_book_store_'. $novel_id . '_'. $user_id;
                    Cache::forget($key);
                }
            } else {
                $key = config('app.name') . 'is_book_store_'. $novel_ids . '_'. $user_id;
                Cache::forget($key);
            }
        }

        $key = config('app.name') . 'book_stored_'.$user_id;
        Cache::forget($key);
    }
    /**
     * 获取最近阅读缓存
     * @param int $user_id
     * @param int $novel_id
     * @param array $cache
     *
     */
    public function GetBookStoreLog($user_id, $novel_id) {
        $key = config('app.name') . 'is_book_store_'. $novel_id . '_'. $user_id;

        // 获取缓存
        return Cache::remember($key, 1440, function () use ($user_id, $novel_id) {
            $cache = $this->findByMap([['user_id', $user_id], ['novel_id', $novel_id]], $this->model->fields);
            return $this->toArr($cache);
        });
    }
    /**
     * 查询书架的小说数量
     * @param int $user_id
     */
    public function BookStoreNum($user_id) {
        $key = config('app.name') . 'book_stored_'.$user_id;
        return Cache::remember($key, 1440, function ()use ($user_id){
            $list = $this->allByMap([
                ['user_id', $user_id],
                ['status', 1],
            ], ['id']);

            return count($list);
        });
    }
}