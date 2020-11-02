<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Goods;
use App\Logics\Repositories\Repository;

class GoodsRepository extends Repository {
	public function model() {
		return Goods::class;
	}
    /**
     * 获取所有的打赏商品
     */
	public function GoodsList() {
	    $list = $this->getAllByMap([['status', 1], [ 'stock', '>', 0]], $this->model->fields, ['sort'=>'asc']);

	    return $this->toArr($list);
    }
}