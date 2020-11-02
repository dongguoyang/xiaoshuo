<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\ReadLog;
use App\Logics\Repositories\Repository;
use App\Logics\Traits\RedisCacheTrait;
use Illuminate\Support\Facades\Cache;

class ReadLogRepository extends Repository {
    use RedisCacheTrait;

    public $abnormal_log = true; // 是否添加不正经小说的阅读记录

	public function model() {
		return ReadLog::class;
	}
    /**
     * 小说阅读记录
     */
    public function UserList($user_id, $page) {
        $key = config('app.name') . 'read_log_'.$user_id . '_'. $page;
        // Cache::forget($key);
        return Cache::remember($key, 1440, function() use ($user_id, $page) {
            $offset = ($page - 1) * $this->pagenum;

            $list = $this->model->with('novel:id,img')
                ->where('user_id', $user_id)
                ->where('status', 1)
                ->orderBy('updated_at', 'desc')
                ->offset($offset)
                ->limit($this->pagenum)
                ->select(['id', 'novel_id', 'name', 'end_section_num', 'max_section_num', 'title', 'updated_at'])
                ->get();
            return $this->toArr($list);
        });
    }
    /**
     * 清除分页列表缓存
     */
    public function ClearCache($user_id) {
        $page = 1;
        while ($page < 15) {
            $key = config('app.name') . 'read_log_'.$user_id . '_'. $page;
            if (!Cache::has($key)) {
                break;
            }
            Cache::forget($key);
            $page++;
        }
    }

    /**
     * 小说阅读记录
     * @param array $novel
     * @param array $section
     * @param array $user
     */
    public function AddLog($novel, $section, $user, $pre_content = 0) {
        $user_id = $user['id'];
        // 不存在用户 就不加阅读记录
        if (!$user_id) { return false; }

        $status = 1;
        // 是否添加不正经小说的阅读记录
        if (!$this->abnormal_log && $novel['status'] != 1) {
            // 不正经小说加的阅读记录为停用，不让他后续找到
            $status = 0;
        }

        $end_section_num = $section['num'];
        if ($pre_content == 1 && $end_section_num > 1) {
            $end_section_num--;
        }
        $data = [
            'user_id'   => $user_id,
            'novel_id'  => $section['novel_id'],
            'name'      => $novel['title'],
            'novel_section_id'  => 0,
            'end_section_num'   => $end_section_num,
            // 'max_section_num'   => 0,
            'title'     => $section['title'],
            'status'    => $status,
            'customer_id'           => $user['customer_id'],
            'platform_wechat_id'    => $user['platform_wechat_id'],
            'view_cid'              => $user['view_cid'],
        ];

        $cache = $this->GetReadLog($user_id, $section['novel_id']);

        if ($cache && $cache['max_section_num'] > $section['num']) {
            $data['max_section_num'] = $cache['max_section_num'];
        } else {
            $data['max_section_num'] = $section['num'];
        }
        if ($cache) {
            if (strpos($cache['sectionlist'], ','.$section['num'].',') === false) {
                $data['sectionlist'] = $cache['sectionlist'] . $section['num'] . ',';
            }
            $this->update($data, $cache['id']);
            $this->ClearCache($user_id);
            foreach ($data as $k=>$v) {
                $cache[$k] = $v;
            }
        } else {
            $cache['sectionlist'] = ','. $section['num'] . ',';
            $data['sectionlist'] = $section['num'] . ',';
            $cache = $this->create($data);
        }
        $this->GetReadLog($user_id, $section['novel_id'], $cache);
    }
    /**
     * 获取最近阅读缓存
     * @param int $user_id
     * @param int $novel_id
     * @param array $cache
     *
     */
    public function GetReadLog($user_id, $novel_id, $cache = []) {
        $key = config('app.name') . 'read_log_ids_'.$user_id .'_'. $novel_id;
        if ($cache) {
            // 刷新缓存
            Cache::forget($key);
            return Cache::remember($key, 30, function () use ($cache) {
                return $cache;
            });
        }

        // 获取缓存
        return Cache::remember($key, 30, function () use ($user_id, $novel_id) {
            $cache = $this->findByMap([['user_id', $user_id], ['novel_id', $novel_id]], $this->model->fields);
            return $this->toArr($cache);
        });
    }
    /**
     * 记录最大章节信息
     */
    public function MaxSectionLog($user, $section = []) {
        if (!$user['id']) return false;

        $key = config('app.name') . 'read_log_u'.$user['id'];
        $cache = $this->LmCache($key, '', 0, 4);// 获取缓存数据
        // $cache = json_decode($cache, 1);
        if (!$section) {
            // 没有章节信息表示获取信息
            return $cache;
        }

        // 设置用户阅读记录信息
        if (isset($cache[$section['novel_id']])) {
            $info = $cache[$section['novel_id']];
            $info['end'] = $section['num'];
            $info['max'] = ($info['max'] > $section['num'] || $info['max'] == -1) ? $info['max'] : $section['num'];
        } else {
            $info['end'] = $section['num'];
            $info['max'] = $section['num'];
        }
        $cache[$section['novel_id']] = $info;
        $this->LmCache($key, $cache, 86400*300, 4);
        $this->setDefaultRedisDb();
    }

}