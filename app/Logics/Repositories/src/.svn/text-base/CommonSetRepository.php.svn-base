<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\CommonSet;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class CommonSetRepository extends Repository {
	public function model() {
		return CommonSet::class;
	}

    /**
     * 获取类型的所有值
     * @param string $type
     * @param string $name
     * @return mixed
     */
    public function values($type, $name = '') {
        if ($name) {
            return $this->typeNameValue($type, $name);
        } else {
            return $this->typeName2Value($type);
        }
    }
    /**
     * 获取类型的所有值
     * @param string $type
     * @param string $name
     * @return array
     */
    public function typeName2Value($type) {
        $key = $this->getCacheKey($type);
        $list = Cache::remember($key, 1440, function () use ($type){
            $list = $this->allByMap([
                ['type', '=', $type],
                ['status', '=', 1],
            ], $this->model->fields);

            return $this->toPluck($list, 'value', 'name');
        });
        return $list;
    }
    /**
     * 获取类型名称的唯一值
     * @param string $type
     * @param string $name
     * @return mixed
     */
    public function typeNameValue($type, $name) {
        $list = $this->typeName2Value($type);

        return isset($list[$name]) ? $list[$name] : null;
    }


    /**
     * 获取类型的详细信息 ['name','value','title']
     * @param string $type
     * @param string $name
     * @return mixed
     */
    public function Details($type, $name = '') {
        $key = $this->getCacheKey($type);
        $list = Cache::remember($key, 1440, function () use ($type){
            $list = $this->allByMap([
                ['type', '=', $type],
                ['status', '=', 1],
            ], ['name','value','title']);
            $list = $this->toArr($list);

            $rel = [];
            foreach ($list as $v) {
                $rel[$v['name']] = $v;
            }

            return $rel;
        });

        if ($name) {
            return isset($list[$name]) ? $list[$name] : null;
        }
        return $list;
    }

    public function getCacheKey($type) {
        return config('app.name') . 'common_set_details_type_'.$type;
    }
}