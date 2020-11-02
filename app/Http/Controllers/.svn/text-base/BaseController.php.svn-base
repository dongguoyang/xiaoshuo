<?php
namespace App\Http\Controllers;

use App\Logics\Traits\ApiResponseTrait;

class BaseController extends Controller {
	use ApiResponseTrait;
	public $service; // service 对象

    public function __construct()
    {
        $customer_id = 1;
        cookie('front_customer', $customer_id);
    }
}