<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Type;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class TypeRepository extends Repository {
	public function model() {
		return Type::class;
	}
    /**
     * 获取分类树形数组
     */
	public function typeTree() {
	    $key = config('app.name') . 'novel_type_tree';

	    return Cache::remember($key, 1440, function (){
            $data = [
                [
                    'id'    => 1,
                    'name'  => '性别',
                    'categories'    => [],
                ], [
                    'id'    => 2,
                    'name'  => '小说分类',
                    'categories'    => [],
                ], [
                    'id'    => 3,
                    'name'  => '其他分类',
                    'categories'    => [],
                ],
            ];
	        $list = $this->model
                ->where('status', 1)
                ->orderBy('pid')
                ->orderBy('sort')
                ->select($this->model->fields)
                ->get()
                ->toArray();
	        foreach ($list as $k=>$v) {
                $v['category_id'] = $v['level'];
	            switch ($v['level']) {
                    case 1:
                        $data[0]['categories'][] = $v;
                        break;
                    case 2:
                        $data[1]['categories'][] = $v;
                        break;
                    default:
                        $data[2]['categories'][] = $v;
                        break;
                }
            }
            // $data = array_values($data); // 重置索引

	        return $data;
        });
    }
}