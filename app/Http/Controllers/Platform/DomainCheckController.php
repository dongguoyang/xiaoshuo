<?php
namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Logics\Services\src\DomainService;

class DomainCheckController extends Controller {
	public function __construct(DomainService $service) {
		$this->service = $service;
	}

	public function test() {
		dd($this->service->test());
	}
    // 普通域名检测   /domaincheck/{type_id}   公司内部使用
    public function domain($type_id) {
        return $this->service->domainCH($type_id);
    }
    // 普通域名检测   /domaincheck/{type_id}   公司内部使用
    public function domainBuyed($type_id) {
        return $this->service->domainBuyed($type_id);
    }
    // 仅检测域名检测   /justcheckdomain
    public function justCheckDomain() {
        return $this->service->justCheckDomain();
    }
    // 接收客户检测域名   /insertcheckdomain
    public function insertCheckDomain() {
        return $this->service->insertCheckDomain();
    }

    public function newCheck(){
        return $this->service->new_check();
    }


	// 开放平台域名检测   /domaincheck/platform
	public function platform() {
		return $this->service->platformCH();
	}
	// 公众号域名检测   /domaincheck/wechat
	public function wechat() {
		return $this->service->wechatCH();
	}
    // 支付公众号域名检测   /domaincheck/paywechat
    public function payWechat() {
        return $this->service->payWechatCH();
    }
	// 开放定时切换检测   /domaincheck/plating
	public function plating() {
		return $this->service->plating();
	}
}