<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Customer;
use App\Logics\Repositories\Repository;

class CustomerRepository extends Repository {
	public function model() {
		return Customer::class;
	}
}