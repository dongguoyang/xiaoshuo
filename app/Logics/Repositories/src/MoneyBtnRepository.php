<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\MoneyBtn;
use App\Logics\Repositories\Repository;

class MoneyBtnRepository extends Repository {
	public function model() {
		return MoneyBtn::class;
	}
}