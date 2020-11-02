<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\TemplateMsg;
use App\Logics\Repositories\Repository;

class TemplateMsgRepository extends Repository {
	public function model() {
		return TemplateMsg::class;
	}
    /**
     * 获取所有的打赏商品
     */
	public function CustomerTypeTemp($type, $customer_id=0, $platform_wechat_id=0) {
	    $map = [
	        ['type', $type],
        ];
        if ($customer_id) {
            $map[] = ['customer_id', $customer_id];
        }
        if ($platform_wechat_id) {
            $map[] = ['platform_wechat_id', $platform_wechat_id];
        }
        if (count($map)<2) {
            return [];
        }
        $list = $this->findByMap($map, $this->model->fields);
	    return $this->toArr($list);
    }


    /**
     * 替换模板中的字符串
     */
    public function ReplaceTempKeyword($str, $replace, $search = null) {
        if ($search !== null) {
            // 查询指定字符串并替换
            $str = str_replace($search, $replace, $str);
            return $str;
        }

        $searchs = [
            '{type}',
            '{money}',
            '{status}',
            '{username}',
            '{date}',
            '{datetime}',
        ];
        foreach ($searchs as $v) {
            if (strpos($str, $v)!==false && isset($replace[trim($v, '{}')])) {
                str_replace($v, $replace[trim($v, '{}')], $str);
            }
        }
    }
}