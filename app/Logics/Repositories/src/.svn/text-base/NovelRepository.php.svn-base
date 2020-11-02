<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Novel;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class NovelRepository extends Repository {
	public function model() {
		return Novel::class;
	}
	/**
	 * 获取小说信息
     * @param int $id
     * @return array
     */
	public function NovelInfo($id) {
	    $key = config('app.name') . 'novel_info_'. $id;
	    return Cache::remember($key, 60, function () use ($id) {
	        $data = $this->findByMap([
	            ['id', $id],
                ['status', '>', 0],
            ], $this->model->fields);
	        return $this->toArr($data);
        });
    }
    /**
     * 打赏的时候增加小说获得的总书币数
     * @param int $id
     * @param int $coin
     */
    public function AddRewardCoin($id, $coin) {
	    $novel = $this->NovelInfo($id);
	    if ($this->update(['reward_coin'=>($novel['reward_coin'] + $coin)], $id)) {
            $key = config('app.name') . 'novel_info_'. $id;
	        Cache::forget($key);
        }
    }

    /**
     * 获取搜索推荐小说列表；没有用的废弃方式
     */
    /*public function SearchRecommend() {
        $key = config('app.name').'novel_search_recommend_list';
        return Cache::remember($key, 1440, function () {
            $list = $this->model
                ->where('status', 1)
                ->where('search_recommend', 1)
                ->orderBy('updated_at', 'desc')
                ->limit($this->pagenum)
                ->select(['title'])
                ->get();
            return $this->toArr($list);
        });
    }*/
    /**
     * 获取推荐小说列表；没有用的废弃方式
     */
    /*public function IsRecommend() {
        $key = config('app.name').'novel_recommend_list';
        return Cache::remember($key, 1440, function () {
            $list = $this->model
                ->where('status', 1)
                ->where('is_recommend', 1)
                ->orderBy('updated_at', 'desc')
                ->limit($this->pagenum)
                ->select($this->model->fields)
                ->get();
            return $this->toArr($list);
        });
    }*/
    /**
     * 搜索小说
     * @param string $search_item 搜索关键字
     * @param string $search_key 搜索关键字的值
     */
    public function Search($search) {
        $key = config('app.name').'novel_search_list';
        foreach ($search as $k=>$v) {
            $key .= '_' . $k . '=' . $v;
        }
        return Cache::tags(['novel_list'])->remember($key, 5, function () use ($search) {
            $list = $this->SearchList($search);
            return $this->toArr($list);
        });
    }
    private function SearchList($search) {
        if (isset($search['page'])) {
            $page = $search['page'] ?: 1;
            unset($search['page']);
        } else {
            $page = 1;
        }
        $page--;

        // 标题查询
        if (isset($search['title']) && trim($search['title'])!=='') {
            return $this->model->where('status', 1)->where('title', 'like', '%'.$search['title'].'%')->offset($this->pagenum * $page)->limit($this->pagenum)->select($this->model->fields)->get();
        }

        // 出版书查询
        if (isset($search['published']) && $search['published']) {
            return $this->model->where('status', 1)->whereIn('type_ids',['9', '17'])->offset($this->pagenum * $page)->limit($this->pagenum)->select($this->model->fields)->get();
        }

        // 限时免费查询
        if (isset($search['free']) && $search['free']) {
            return $this->model->where('status', 1)->where('allbuy_coin', '>', 0)->where('free_start_at', '<', time())->where('free_end_at', '>', time())->offset($this->pagenum * $page)->limit($this->pagenum)->select(array_merge($this->model->fields, ['free_start_at', 'free_end_at']))->get();
        }

        // 榜单查询
        if (isset($search['order']) && $search['order']) {
            if ($page > 0) {
                return null;
            }
            switch ($search['order']) {
                case 'week_read_num':
                    return $this->model->where('status', 1)->orderBy('week_read_num', 'desc')->limit(20)->select($this->model->fields)->get();
                case 'read_num':
                    return $this->model->where('status', 1)->where('updated_at', '>', (time() - 86400 * 7))->orderBy('read_num', 'desc')->limit(20)->select($this->model->fields)->get();
                case 'created_at':
                    return $this->model->where('status', 1)->orderBy('created_at', 'desc')->limit(20)->select($this->model->fields)->get();
                case 'is_original':
                    $indexPageRep = new IndexPageRepository();
                    $origin_ids = $indexPageRep->model->where('type', 10)->select(['novel_id'])->get();
                    $origin_ids = $indexPageRep->toArr($origin_ids);
                    return $this->model->where('status', 1)->whereIn('id', $origin_ids)->orderBy('week_read_num', 'desc')->limit(20)->select($this->model->fields)->get();
                default:
                    return null;
            }
        }

        // 一般的分类查询
        unset($search['title']);
        unset($search['order']);
        unset($search['default']);
        $map_key = ['author_id', 'serial_status', 'suitable_sex', 'type_id'];
        $map = [['status', 1]];
        foreach ($search as $k=>$v) {
            if (in_array($k, $map_key)) {
                if ($k == 'type_id') {
                    $map[] = ['type_ids', $v];
                } else {
                    $map[] = [$k, $v];
                }
            }
        }
        /*if (!$map) {
            throw new \Exception('查询条件异常！', 2003);
        }*/
        return $this->model
            ->where($map)
            ->offset($this->pagenum * $page)
            ->limit($this->pagenum)
            ->orderBy('hot_num', 'desc')
            ->orderBy('updated_at', 'desc')
            ->select($this->model->fields)
            ->get();
    }
    /**
     * 章节详情的推荐列表
     */
    public function SectionRecommendList($type) {
        $key = config('app.name') . 'section_push_type' . $type;

        return Cache::tags(['novel_list'])->remember($key, 1440, function () use ($type) {
            $list = $this->model->where('status', 1)
                ->where('type_ids', $type)
                ->orderBy('hot_num', 'desc')
                ->limit($this->pagenum)
                ->select(['id', 'title', 'hot_num', 'desc'])
                ->get();
            $list = $this->toArr($list);
            if (!$list) {
                $list = $this->model->where('status', 1)
                    ->orderBy('hot_num', 'desc')
                    ->orderBy('read_num', 'desc')
                    ->limit($this->pagenum)
                    ->select(['id', 'title', 'hot_num', 'desc'])
                    ->get();
                $list = $this->toArr($list);
            }
            return $list;
        });
    }
    /**
     * 清除小说列表缓存
     */
    public function ClearCache() {
        Cache::tags(['novel_list'])->flush();
    }
}