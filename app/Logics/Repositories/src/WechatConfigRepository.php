<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\WechatConfig;
use App\Logics\Repositories\Repository;

class WechatConfigRepository extends Repository {
	public function model() {
		return WechatConfig::class;
	}

    /**
     * 微信配置信息
     * @param string $component_appid
     * @param string $origin_id
     */
    public function FindForComponentAppID($component_appid, $appid) {
        $platformWechatRep = new PlatformWechatRepository();
        $wechat = $platformWechatRep->getWechatForCAPPID2APPID($component_appid, $appid);

        return $this->FindByCustomer2PlatWechat($wechat['customer_id'], $wechat['id']);
    }

    public function FindByCustomer2PlatWechat($customer_id, $platform_wechat_id) {
        $info = $this->findByMap([
            ['customer_id', $customer_id],
            ['platform_wechat_id', $platform_wechat_id],
        ], $this->model->fields);

        $info = $this->toArr($info);
        $info['pushconf'] = json_decode($info['pushconf'], 1);
        $info['search_sub'] = json_decode($info['search_sub'], 1);
        return $info;
    }
    public function FindByCustomerID($customer_id) {
        $wechatRep = new WechatRepository();

        $wechat = $wechatRep->findByMap([
            ['customer_id', $customer_id],
            ['status', 1],
        ], ['id']);

        if (!$wechat) return null;

        return $this->FindByCustomer2PlatWechat($customer_id, $wechat['id']);
    }
}