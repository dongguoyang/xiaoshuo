<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\WechatMsgReply;
use App\Logics\Repositories\Repository;

class WechatMsgReplyRepository extends Repository {
	public function model() {
		return WechatMsgReply::class;
	}
    /**
     * 获取关键字回复
     * @param string $component_appid
     * @param string $origin_id
     */
	public function KeyWordMsg($appid, $keyword) {
	    $wechatRep = new WechatRepository();
        $wechat = $wechatRep->getWechatForAppid($appid);
        $info = $this->findByMap([
            ['customer_id', $wechat['customer_id']],
            ['platform_wechat_id', $wechat['id']],
            ['keyword', trim($keyword)],
        ], $this->model->fields);

        return $this->toArr($info);
    }
}