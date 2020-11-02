<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\CommonText;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class CommonTextRepository extends Repository {
	public function model() {
		return CommonText::class;
	}

    /**
     * 根据ID 获取公共长文本配置
     */
    public function FindById($id)
    {
        $key = config('app.name') . 'common_text_id_'.$id;
        $info = Cache::remember($key, 1440, function () use ($id){
            $info = $this->findByMap([
                ['id', $id],
                ['status', 1],
            ], ['title', 'value']);
            return $this->toArr($info);
        });
        return $info;
    }

    /**
     * 根据 type   name 获取公共长文本配置
     */
    public function FindByTypeName($type, $name)
    {
        $key = config('app.name') . 'common_text_type_'.$type.'_name_'.$name;
        $info = Cache::remember($key, 1440, function () use ($type, $name){
            $info = $this->findByMap([
                ['type', $type],
                ['name', $name],
                ['status', 1],
            ], ['title', 'value']);
            return $this->toArr($info);
        });
        return $info;
    }
}