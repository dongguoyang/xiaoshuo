<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\DomainType;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class DomainTypeRepository extends Repository {
	public function model() {
		return DomainType::class;
	}
    /**
     * 获取域名分类信息
     */
    public function typeInfo($type) {
        $key = config('app.name') . 'domain_type_info_' . $type;

        $info = Cache::remember($key, 1440, function () use ($type) {
            $info = $this->find($type, $this->model->fields);
            if ($info) {
                $limit = $info['rand_num'] ? $info['rand_num'] : 10;
                $info->rand_num = $limit;
                $info->pre = trim($info->pre);
            }
            return $info;
        });

        return $info;
    }
}
