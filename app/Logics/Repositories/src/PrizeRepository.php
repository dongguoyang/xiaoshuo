<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Prize;
use App\Logics\Repositories\Repository;

class PrizeRepository extends Repository {
	public function model() {
		return Prize::class;
	}
}