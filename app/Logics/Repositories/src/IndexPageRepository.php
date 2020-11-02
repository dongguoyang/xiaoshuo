<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\IndexPage;
use App\Logics\Repositories\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class IndexPageRepository extends Repository {
    private $novelRep;
    private $cacheKey;

	public function model() {
		return IndexPage::class;
	}

    private $customer_pid; // 客户PID
    private $customer_id; // 客户ID
	private $platform_wechat_id;// 公众号ID
    /**
     * 首页数据获取
     * @param int $customer_id
     * @param int $platform_wechat_id
     */
    public function IndexData($customer_id, $platform_wechat_id) {
        $this->platform_wechat_id = $platform_wechat_id;
        $this->customer_id        = $customer_id;
        //$this->cacheKey           = config('app.name') . 'index_page_data_'.$customer_id.'_'.$platform_wechat_id;
        $this->cacheKey           = config('app.name') . 'index_page_data_'.$customer_id;

        list($rel['man']['carousel'], $rel['woman']['carousel']) = $this->carouselImgs(); // 轮播图数据
        list($rel['man']['hot'], $rel['woman']['hot']) = $this->hotData(); // 本周热门阅读数据
        list($rel['man']['recommend'], $rel['woman']['recommend']) = $this->authRecommendData(); // 主编推荐数据
        list($rel['man']['weeknews'], $rel['woman']['weeknews']) = $this->weekNewData(); // 本周新书数据
        list($rel['man']['published'], $rel['woman']['published']) = $this->publishedData(); // 出版物数据
        list($rel['man']['selection'], $rel['woman']['selection']) = $this->fineSelectionData(); // 精选数据

        return $rel;

    }
    /**
     * 首页数据获取更多
     * @param int $customer_id
     * @param int $platform_wechat_id
     */
    public function IndexDataMore($customer_id, $platform_wechat_id, $type, $page) {
        // Cache::flush();
        $this->platform_wechat_id = $platform_wechat_id;
        $this->customer_id        = $customer_id;
        //$this->cacheKey           = config('app.name') . 'index_page_data_more_'.$customer_id.'_'.$platform_wechat_id;
        $this->cacheKey           = config('app.name') . 'index_page_data_more_'.$customer_id;
        switch ($type) {
            case 'hot':
                list($rel['man'], $rel['woman']) = $this->hotData($page); // 本周热门阅读数据
                break;
            case 'recommend':
                list($rel['man'], $rel['woman']) = $this->authRecommendData($page); // 主编推荐数据
                break;
            case 'weeknews':
                list($rel['man'], $rel['woman']) = $this->weekNewData($page); // 本周新书数据
                break;
            case 'published':
                list($rel['man'], $rel['woman']) = $this->publishedData($page); // 出版物数据
                break;
            case 'selection':
                list($rel['man'], $rel['woman']) = $this->fineSelectionData($page); // 精选数据
                break;
            default:
                throw new \Exception('类型错误！', 2000);
                break;
        }

        return $rel;
    }
    /**
     * 搜索页默认数据获取
     * @param int $customer_id
     * @param int $platform_wechat_id
     */
    public function SearchDefaultData($customer_id, $platform_wechat_id) {
        $this->platform_wechat_id = $platform_wechat_id;
        $this->customer_id        = $customer_id;
        $key = config('app.name').'novel_search_recommend_list_'.$customer_id.'_'.$platform_wechat_id;

        return Cache::tags(['novel_list'])->remember($key, 1440, function (){
            $rel['title_list'] = $this->titleRecommendData(); // 搜索页标题推荐数据
            $rel['novel_list'] = $this->novelRecommendData(); // 搜索也小说推荐数据

            return $rel;
        });
    }
    /**
     * 书架推荐数据
     */
    public function bookStoresRecommendData($platform_wechat_id, $customer_id) {
        $this->platform_wechat_id = $platform_wechat_id;
        $this->customer_id        = $customer_id;
        $key = config('app.name').'bookstores_recommend_list_'.$customer_id.'_'.$platform_wechat_id;

        return Cache::tags(['novel_list'])->remember($key, 1440, function (){
            list($man_rel, $woman_rel, $ids0) = $this->dataNovelInfo(9);

            $rel = array_merge($man_rel, $woman_rel);
            $ids = array_merge($ids0['man'], $ids0['woman']);

            // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
            if (!$ids || count($ids) < 4) {
                $query = $this->initNovelRep()->model->where('status', '>', 0);
                if (isset($ids)) {
                    $query = $query->whereNotIn('id', $ids);
                }
                $data = $query->orderBy('week_read_num', 'desc')
                    ->select(['img', 'title', 'desc', 'tags', 'suitable_sex', 'id as novel_id'])
                    ->offset(6)->limit(6)->get();
                $rel = array_merge($rel, $this->initNovelRep()->toArr($data));
            }
            return $rel;
        });
    }

    /**
     * 原创榜推荐数据
     */
    /*public function originalRecommendData($platform_wechat_id, $customer_id) {
        $this->platform_wechat_id = $platform_wechat_id;
        $this->customer_id        = $customer_id;
        $key = config('app.name').'original_recommend_list_'.$customer_id.'_'.$platform_wechat_id;

        return Cache::remember($key, 1440, function (){
            list($man_rel, $woman_rel, $ids0) = $this->dataNovelInfo(9);

            $rel = array_merge($man_rel, $woman_rel);
            $ids = array_merge($ids0['man'], $ids0['woman']);

            // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
            if (!$ids || count($ids) < 4) {
                $query = $this->initNovelRep()->model->where('status', 1);
                if (isset($ids)) {
                    $query = $query->whereNotIn('id', $ids);
                }
                $data = $query->orderBy('week_read_num', 'desc')
                    ->select(['img', 'title', 'desc', 'tags', 'suitable_sex', 'id as novel_id'])
                    ->offset(156)->limit(6)->get();
                $rel = array_merge($rel, $this->initNovelRep()->toArr($data));
            }
            return $rel;
        });
    }*/
    /**
     * 获取轮播图数据
     */
    private function carouselImgs($page = 1) {
        return Cache::tags(['novel_list'])->remember($this->cacheKey.'carousel'.$page, 1440, function (){
            $map = [
                ['customer_id', $this->customer_id],
                ['platform_wechat_id', $this->platform_wechat_id],
                ['type', 1],
            ];
            $list = $this->allByMap($map, ['suitable_sex', 'novel_id', 'img']);
            if (!count($list)) {
                if (!$this->customer_pid) {
                    $this->setCustomerPId();
                }
                $map = [
                    ['customer_id', $this->customer_pid],
                    ['type', 1],
                ];
                $list = $this->allByMap($map, ['suitable_sex', 'novel_id', 'img']);
            }

            $novel_ids = $this->toPluck($list, 'novel_id');
            $novelRep = new  NovelRepository();
            $novellist = $novelRep->model->whereIn('id', $novel_ids)->where('status', '>', 0)->select(['id', 'sections'])->get();
            $novellist = $novelRep->toPluck($novellist, 'id');

            $list = $this->toArr($list);
            $man_rel = $woman_rel = [];
            foreach ($list as $v) {
                if (!in_array($v['novel_id'], $novellist)) continue;
                //$v['img'] = 'https://www.zhengzhiwen.net/'.$v['img'];
                if ($v['suitable_sex'] == 1) {
                    $man_rel[] = $v;
                } else {
                    $woman_rel[] = $v;
                }
            }

            if ($man_rel && !$woman_rel) {
                $woman_rel = $man_rel;
            } elseif (!$man_rel && $woman_rel) {
                $man_rel = $woman_rel;
            }
            return [$man_rel, $woman_rel];
        });
    }

    /**
     * 获取本周热门数据
     */
    private function hotData($page = 1) {
        return Cache::tags(['novel_list'])->remember($this->cacheKey.'hot'.$page, 1440, function () use ($page) {
            list($man_rel, $woman_rel, $ids) = $this->dataNovelInfo(2);

            // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
            list($man_rel, $woman_rel) = $this->defaultNovelInfo($man_rel, $woman_rel, $ids, ['week_read_num'=>'desc'], [], $page);

            /*$man_rel = array_slice($man_rel, ($page-1)*$this->pagenum, $this->pagenum);
            $woman_rel = array_slice($woman_rel, ($page-1)*$this->pagenum, $this->pagenum);*/

            return [$man_rel, $woman_rel];
        });
    }
    /**
     * 主编推荐数据
     */
    private function authRecommendData($page = 1) {
        return Cache::tags(['novel_list'])->remember($this->cacheKey.'recommend'.$page, 1440, function () use ($page) {
            list($man_rel, $woman_rel, $ids) = $this->dataNovelInfo(3);

            // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
            list($man_rel, $woman_rel) = $this->defaultNovelInfo($man_rel, $woman_rel, $ids, ['read_num'=>'desc'], [], $page);

            /*$man_rel = array_slice($man_rel, ($page-1)*$this->pagenum, $this->pagenum);
            $woman_rel = array_slice($woman_rel, ($page-1)*$this->pagenum, $this->pagenum);*/

            return [$man_rel, $woman_rel];
        });
    }
    /**
     * 本周新书数据
     */
    private function weekNewData($page = 1) {
        return Cache::tags(['novel_list'])->remember($this->cacheKey.'weeknews'.$page, 1440, function () use ($page) {
            list($man_rel, $woman_rel, $ids) = $this->dataNovelInfo(4);

            // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
            list($man_rel, $woman_rel) = $this->defaultNovelInfo($man_rel, $woman_rel, $ids, ['week_read_num'=>'desc'], [
                'man'   => [    ['created_at', '>', Carbon::getWeekStartsAt()]  ],
                'woman' => [    ['created_at', '>', Carbon::getWeekStartsAt()]  ],
            ], $page);

            /*$man_rel = array_slice($man_rel, ($page-1)*$this->pagenum, $this->pagenum);
            $woman_rel = array_slice($woman_rel, ($page-1)*$this->pagenum, $this->pagenum);*/

            return [$man_rel, $woman_rel];
        });
    }
    /**
     * 出版物图书数据
     */
    private function publishedData($page = 1) {
        return Cache::tags(['novel_list'])->remember($this->cacheKey.'published'.$page, 1440, function () use ($page) {
            list($man_rel, $woman_rel, $ids) = $this->dataNovelInfo(5);

            // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
            list($man_rel, $woman_rel) = $this->defaultNovelInfo($man_rel, $woman_rel, $ids, ['read_num'=>'desc'], [
                'man'   => [    ['type_ids', 9]  ],
                'woman' => [    ['type_ids', 17]  ],
            ], $page);

            /*$man_rel = array_slice($man_rel, ($page-1)*$this->pagenum, $this->pagenum);
            $woman_rel = array_slice($woman_rel, ($page-1)*$this->pagenum, $this->pagenum);*/

            return [$man_rel, $woman_rel];
        });
    }
    /**
     * 男女生精选图书数据
     */
    private function fineSelectionData($page = 1) {
        return Cache::tags(['novel_list'])->remember($this->cacheKey.'selection'.$page, 1440, function () use ($page) {
            list($man_rel, $woman_rel, $ids) = $this->dataNovelInfo(6);

            // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
            list($man_rel, $woman_rel) = $this->defaultNovelInfo($man_rel, $woman_rel, $ids, ['word_count'=>'desc'], [], $page);

            /*$man_rel = array_slice($man_rel, ($page-1)*$this->pagenum, $this->pagenum);
            $woman_rel = array_slice($woman_rel, ($page-1)*$this->pagenum, $this->pagenum);*/

            return [$man_rel, $woman_rel];
        });
    }
    /**
     * 标题推荐数据
     */
    private function titleRecommendData() {
        list($man_rel, $woman_rel, $ids0) = $this->dataNovelInfo(8);

        $rel = array_merge($man_rel, $woman_rel);
        $ids = array_merge($ids0['man'], $ids0['woman']);

        // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
        if (!$ids || count($ids) < 4) {
            $query = $this->initNovelRep()->model->where('status', '>', 0);
            if (isset($ids)) {
                $query = $query->whereNotIn('id', $ids);
            }
            $data = $query->orderBy('week_read_num', 'desc')
                ->select(['img', 'title', 'desc', 'tags', 'suitable_sex', 'id as novel_id'])
                ->limit(6)->get();
            $rel = array_merge($rel, $this->initNovelRep()->toArr($data));
        }
        return $rel;
    }
    /**
     * 搜索页小说推荐数据
     */
    private function novelRecommendData() {
        list($man_rel, $woman_rel, $ids0) = $this->dataNovelInfo(7);
        $rel = array_merge($man_rel, $woman_rel);
        $ids = array_merge($ids0['man'], $ids0['woman']);

        // 没有配置本周热门数据时；直接获取本周阅读量最高的数据
        if (!$ids || count($ids) < 4) {
            $query = $this->initNovelRep()->model->where('status', '>', 0);
            if (isset($ids)) {
                $query = $query->whereNotIn('id', $ids);
            }
            $data = $query->orderBy('week_read_num', 'desc')
                ->select(['img', 'title', 'desc', 'tags', 'suitable_sex', 'id as novel_id'])
                ->limit(6)->get();
            $rel = array_merge($rel, $this->initNovelRep()->toArr($data));
        }
        return $rel;
    }

    private function initNovelRep() {
        if (!isset($this->novelRep) || !$this->novelRep) {
            $this->novelRep = new NovelRepository();
        }

        return $this->novelRep;
    }
    /**
     * 查询首页数据配置
     */
    private function indexPageInfo($type)
    {
        $list = $this->allByMap([
            ['customer_id', $this->customer_id],
            ['platform_wechat_id', $this->platform_wechat_id],
            ['type', $type],
        ], ['suitable_sex', 'novel_id', 'img']);
        if (!$list || !count($list)) {
            if (!$this->customer_pid) {
                $this->setCustomerPId();
            }
            $list = $this->allByMap([
                ['customer_id', $this->customer_pid],
                ['type', $type],
            ], ['suitable_sex', 'novel_id', 'img']);
        }
        
        $list = $this->toArr($list);
        $ids = $pagedata = [
            'man' => [],
            'woman' => [],
        ];
        foreach ($list as $v) {
            if ($v['suitable_sex'] == 1) {
                $pagedata['man'][$v['novel_id']] = $v;
                $ids['man'][] = $v['novel_id'];
            } else {
                $pagedata['woman'][$v['novel_id']] = $v;
                $ids['woman'][] = $v['novel_id'];
            }
        }

        return [$pagedata, $ids];
    }
    /**
     * 获取首页数据配置的详细信息
     */
    private function dataNovelInfo($type){
        list($pagedata, $ids) = $this->indexPageInfo($type);
        $man_rel = $woman_rel = [];
        // 查询已有数据
        if (isset($ids['man'])) {
            $man_rel = $this->initNovelRep()->model->where('status', '>', 0)->whereIn('id', $ids['man'])->select(['img', 'title', 'desc', 'tags', 'suitable_sex', 'id as novel_id'])->get();
            $man_rel = $this->toArr($man_rel);
            foreach ($man_rel as $k => $v) {
                $man_rel[$k]['img'] = empty($pagedata['man'][$v['novel_id']]['img']) ? $v['img'] : $pagedata['man'][$v['novel_id']]['img'];
            }
        }
        if (isset($ids['woman'])) {
            $woman_rel = $this->initNovelRep()->model->where('status', '>', 0)->whereIn('id', $ids['woman'])->select(['img', 'title', 'desc', 'tags', 'suitable_sex', 'id as novel_id'])->get();
            $woman_rel = $this->toArr($woman_rel);
            foreach ($woman_rel as $k => $v) {
                $woman_rel[$k]['img'] = empty($pagedata['woman'][$v['novel_id']]['img']) ? $v['img'] : $pagedata['woman'][$v['novel_id']]['img'];
            }
        }

        return [$man_rel, $woman_rel, $ids];
    }
    /**
     * 数据不足就查询novel表获取默认数据
     */
    private function defaultNovelInfo($man_rel, $woman_rel, $ids, $order=[], $map=[], $page=1) {
        // 只有第一页没有数据才会查询默认数据
        if ($page == 1 && !isset($ids['man']) || count($ids['man']) < 6) {
            $query = $this->initNovelRep()
                ->model->where('status', '>', 0)
                ->where('suitable_sex', 1);
            if ($map) {
                $query = $query->where($map['man']);
            }

            if (isset($ids['man'])) {
                $query = $query->whereNotIn('id', $ids['man']);
            }
            if ($order) {
                foreach ($order as $k => $v) {
                    $query = $query->orderBy($k, $v);
                }
            }
            $man2 = $query->select(['img', 'title', 'desc', 'tags', 'suitable_sex', 'id as novel_id'])
                ->limit($this->pagenum)->get();
            $man_rel = array_merge($man_rel, $this->initNovelRep()->toArr($man2));
        }
        if ($page == 1 && !isset($ids['woman']) || count($ids['woman']) < 6) {
            $query = $this->initNovelRep()->model->where('status', '>', 0)->where('suitable_sex', 2);
            if ($map) {
                $query = $query->where($map['man']);
            }
            if (isset($ids['woman'])) {
                $query = $query->whereNotIn('id', $ids['woman']);
            }
            if ($order) {
                foreach ($order as $k => $v) {
                    $query = $query->orderBy($k, $v);
                }
            }
            $woman2 = $query->select(['img', 'title', 'desc', 'tags', 'suitable_sex', 'id as novel_id'])
                ->limit(6)->get();
            $woman_rel = array_merge($woman_rel, $this->initNovelRep()->toArr($woman2));
        }

        if ($man_rel && !$woman_rel) {
            $woman_rel = $man_rel;
        } elseif (!$man_rel && $woman_rel) {
            $man_rel = $woman_rel;
        }

        // 截取返还对应页数据
        $man_rel = array_slice($man_rel, ($page-1)*$this->pagenum, $this->pagenum);
        $woman_rel = array_slice($woman_rel, ($page-1)*$this->pagenum, $this->pagenum);

        return [$man_rel, $woman_rel];
    }
    // 实例化 customer_pid
    private function setCustomerPId() {
        $customerRep = new CustomerRepository();
        $customer = $customerRep->find($this->customer_id, ['id', 'pid']);
        if ($customer) {
            $this->customer_pid = $customer['pid'];
        }
    }


}